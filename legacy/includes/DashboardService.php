<?php

declare(strict_types=1);

class DashboardService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getStats(): array
    {
        $reports = (new ReportService())->getAll();
        $byStatus = ['available' => 0, 'low' => 0, 'none' => 0, 'scheduled' => 0];
        $neighborhoodIds = [];

        foreach ($reports as $report) {
            if (isset($byStatus[$report['status']])) {
                $byStatus[$report['status']]++;
            }
            $neighborhoodIds[$report['neighborhoodId']] = true;
        }

        return [
            'total'          => count($reports),
            'byStatus'       => $byStatus,
            'neighborhoods'  => count($neighborhoodIds),
        ];
    }

    public function getWeeklyTrends(?int $neighborhoodId = null): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $available = array_fill(0, 7, 0);
        $low = array_fill(0, 7, 0);
        $none = array_fill(0, 7, 0);

        $sql = "SELECT status, DAYOFWEEK(reported_at) AS dow, COUNT(*) AS cnt
                FROM reports
                WHERE reported_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $params = [];

        if ($neighborhoodId) {
            $sql .= ' AND neighborhood_id = :neighborhood_id';
            $params['neighborhood_id'] = $neighborhoodId;
        }

        $sql .= ' GROUP BY status, DAYOFWEEK(reported_at)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        foreach ($stmt->fetchAll() as $row) {
            $idx = ((int) $row['dow'] + 5) % 7;
            match ($row['status']) {
                'available' => $available[$idx] = (int) $row['cnt'],
                'low'       => $low[$idx] = (int) $row['cnt'],
                'none'      => $none[$idx] = (int) $row['cnt'],
                default     => null,
            };
        }

        return [
            'labels'    => $days,
            'available' => $available,
            'low'       => $low,
            'none'      => $none,
        ];
    }

    public function getMonthlyStats(): array
    {
        $stmt = $this->db->query(
            "SELECT DATE_FORMAT(reported_at, '%b') AS month,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available_cnt,
                    SUM(CASE WHEN status = 'none' THEN 1 ELSE 0 END) AS outage_cnt,
                    COUNT(*) AS total
             FROM reports
             WHERE reported_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY YEAR(reported_at), MONTH(reported_at)
             ORDER BY MIN(reported_at)"
        );

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $total = max(1, (int) $row['total']);
            $result[] = [
                'month'        => $row['month'],
                'availablePct' => (int) round(((int) $row['available_cnt'] / $total) * 100),
                'outages'      => (int) $row['outage_cnt'],
            ];
        }

        if (empty($result)) {
            return [
                ['month' => 'Jan', 'availablePct' => 0, 'outages' => 0],
            ];
        }

        return $result;
    }

    public function getNeighborhoodSummary(): array
    {
        $stmt = $this->db->query(
            'SELECT n.id, n.name, n.area,
                    (SELECT r.status FROM reports r WHERE r.neighborhood_id = n.id
                     ORDER BY r.reported_at DESC LIMIT 1) AS latest_status,
                    (SELECT r.reported_at FROM reports r WHERE r.neighborhood_id = n.id
                     ORDER BY r.reported_at DESC LIMIT 1) AS last_report,
                    (SELECT COUNT(*) FROM reports r WHERE r.neighborhood_id = n.id
                     AND r.reported_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS reports_7d
             FROM neighborhoods n
             ORDER BY n.name'
        );

        return array_map(function ($row) {
            return [
                'id'           => (int) $row['id'],
                'name'         => $row['name'],
                'area'         => $row['area'],
                'latestStatus' => $row['latest_status'],
                'lastReport'   => $row['last_report'] ? date('c', strtotime($row['last_report'])) : null,
                'reports7d'    => (int) $row['reports_7d'],
            ];
        }, $stmt->fetchAll());
    }

    public function getActivityByDay(): array
    {
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $counts = array_fill(0, 7, 0);

        $stmt = $this->db->query(
            "SELECT DAYOFWEEK(reported_at) AS dow, COUNT(*) AS cnt
             FROM reports
             WHERE reported_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DAYOFWEEK(reported_at)"
        );

        foreach ($stmt->fetchAll() as $row) {
            $idx = ((int) $row['dow'] + 5) % 7;
            $counts[$idx] = (int) $row['cnt'];
        }

        return ['labels' => $labels, 'counts' => $counts];
    }
}
