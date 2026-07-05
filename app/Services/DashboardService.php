<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\DB;

/**
 * Aggregation queries for the dashboard. Kept as raw SQL (via the query
 * builder's DB facade) for the weekly/monthly/activity breakdowns because
 * they lean on MySQL-specific date functions (DAYOFWEEK, DATE_FORMAT,
 * DATE_SUB) that Eloquent has no portable equivalent for — and this project
 * is MySQL/MariaDB-only by design, so there's no portability to preserve.
 */
class DashboardService
{
    public function getStats(): array
    {
        $byStatus = ['available' => 0, 'low' => 0, 'none' => 0, 'scheduled' => 0];
        $neighborhoodIds = [];

        foreach (Report::all(['status', 'neighborhood_id']) as $report) {
            if (isset($byStatus[$report->status])) {
                $byStatus[$report->status]++;
            }
            $neighborhoodIds[$report->neighborhood_id] = true;
        }

        return [
            'total' => array_sum($byStatus),
            'byStatus' => $byStatus,
            'neighborhoods' => count($neighborhoodIds),
        ];
    }

    public function getWeeklyTrends(?int $neighborhoodId = null): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $available = array_fill(0, 7, 0);
        $low = array_fill(0, 7, 0);
        $none = array_fill(0, 7, 0);

        $sql = 'SELECT status, DAYOFWEEK(reported_at) AS dow, COUNT(*) AS cnt
                FROM reports
                WHERE reported_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
        $bindings = [];

        if ($neighborhoodId) {
            $sql .= ' AND neighborhood_id = ?';
            $bindings[] = $neighborhoodId;
        }

        $sql .= ' GROUP BY status, DAYOFWEEK(reported_at)';

        foreach (DB::select($sql, $bindings) as $row) {
            $idx = ($row->dow + 5) % 7;
            match ($row->status) {
                'available' => $available[$idx] = (int) $row->cnt,
                'low' => $low[$idx] = (int) $row->cnt,
                'none' => $none[$idx] = (int) $row->cnt,
                default => null,
            };
        }

        return [
            'labels' => $days,
            'available' => $available,
            'low' => $low,
            'none' => $none,
        ];
    }

    public function getMonthlyStats(): array
    {
        $rows = DB::select(
            "SELECT DATE_FORMAT(MIN(reported_at), '%b') AS month,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available_cnt,
                    SUM(CASE WHEN status = 'none' THEN 1 ELSE 0 END) AS outage_cnt,
                    COUNT(*) AS total,
                    MIN(reported_at) AS first_reported
             FROM reports
             WHERE reported_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY YEAR(reported_at), MONTH(reported_at)
             ORDER BY first_reported"
        );

        $result = [];
        foreach ($rows as $row) {
            $total = max(1, (int) $row->total);
            $result[] = [
                'month' => $row->month,
                'availablePct' => (int) round(($row->available_cnt / $total) * 100),
                'outages' => (int) $row->outage_cnt,
            ];
        }

        if (empty($result)) {
            return [['month' => 'Jan', 'availablePct' => 0, 'outages' => 0]];
        }

        return $result;
    }

    public function getNeighborhoodSummary(): array
    {
        $rows = DB::select(
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

        return array_map(fn ($row) => [
            'id' => (int) $row->id,
            'name' => $row->name,
            'area' => $row->area,
            'latestStatus' => $row->latest_status,
            'lastReport' => $row->last_report ? date('c', strtotime($row->last_report)) : null,
            'reports7d' => (int) $row->reports_7d,
        ], $rows);
    }

    public function getActivityByDay(): array
    {
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $counts = array_fill(0, 7, 0);

        $rows = DB::select(
            "SELECT DAYOFWEEK(reported_at) AS dow, COUNT(*) AS cnt
             FROM reports
             WHERE reported_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DAYOFWEEK(reported_at)"
        );

        foreach ($rows as $row) {
            $counts[($row->dow + 5) % 7] = (int) $row->cnt;
        }

        return ['labels' => $labels, 'counts' => $counts];
    }
}
