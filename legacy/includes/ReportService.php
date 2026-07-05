<?php

declare(strict_types=1);

class ReportService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll(array $filters = []): array
    {
        $sql = 'SELECT r.*, u.name AS user_name, n.name AS neighborhood_name
                FROM reports r
                JOIN users u ON u.id = r.user_id
                JOIN neighborhoods n ON n.id = r.neighborhood_id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['neighborhoodId'])) {
            $sql .= ' AND r.neighborhood_id = :neighborhood_id';
            $params['neighborhood_id'] = (int) $filters['neighborhoodId'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND r.status = :status';
            $params['status'] = $filters['status'];
        }
        if (isset($filters['verified']) && $filters['verified'] !== '') {
            $sql .= ' AND r.verified = :verified';
            $params['verified'] = $filters['verified'] === 'true' || $filters['verified'] === '1' ? 1 : 0;
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (n.name LIKE :search OR r.notes LIKE :search OR u.name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= ' ORDER BY r.reported_at DESC';

        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map('formatReportRow', $stmt->fetchAll());
    }

    public function create(int $userId, array $data): array
    {
        $neighborhoodId = (int) ($data['neighborhoodId'] ?? 0);
        $status = $data['status'] ?? '';
        $notes = trim($data['notes'] ?? '');

        $validStatuses = ['available', 'low', 'none', 'scheduled'];
        if (!$neighborhoodId || !in_array($status, $validStatuses, true) || strlen($notes) < 10) {
            throw new InvalidArgumentException('Invalid report data. Notes must be at least 10 characters.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO reports (user_id, neighborhood_id, status, notes)
             VALUES (:user_id, :neighborhood_id, :status, :notes)'
        );
        $stmt->execute([
            'user_id'          => $userId,
            'neighborhood_id'  => $neighborhoodId,
            'status'           => $status,
            'notes'            => $notes,
        ]);

        $reportId = (int) $this->db->lastInsertId();
        return $this->getById($reportId);
    }

    public function getById(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.name AS user_name, n.name AS neighborhood_name
             FROM reports r
             JOIN users u ON u.id = r.user_id
             JOIN neighborhoods n ON n.id = r.neighborhood_id
             WHERE r.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new RuntimeException('Report not found.');
        }
        return formatReportRow($row);
    }

    public function verify(int $reportId): array
    {
        $report = $this->getById($reportId);
        $stmt = $this->db->prepare('UPDATE reports SET verified = 1 WHERE id = :id');
        $stmt->execute(['id' => $reportId]);
        return $this->getById($reportId);
    }

    public function delete(int $reportId): void
    {
        $stmt = $this->db->prepare('DELETE FROM reports WHERE id = :id');
        $stmt->execute(['id' => $reportId]);
    }

    public function countToday(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM reports WHERE DATE(reported_at) = CURDATE()"
        );
        return (int) $stmt->fetchColumn();
    }
}
