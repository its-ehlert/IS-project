<?php

declare(strict_types=1);

class UserService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll(array $filters = []): array
    {
        $sql = 'SELECT u.*, n.name AS neighborhood_name FROM users u
                LEFT JOIN neighborhoods n ON n.id = u.neighborhood_id WHERE 1=1';
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= ' AND u.role = :role';
            $params['role'] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND u.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (u.name LIKE :search OR u.email LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= ' ORDER BY u.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map('formatUserRow', $stmt->fetchAll());
    }

    public function create(array $data): array
    {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $role = $data['role'] ?? 'resident';
        $neighborhoodId = isset($data['neighborhoodId']) && $data['neighborhoodId'] !== ''
            ? (int) $data['neighborhoodId'] : null;

        if ($name === '' || $email === '') {
            throw new InvalidArgumentException('Name and email are required.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role, neighborhood_id)
             VALUES (:name, :email, :hash, :role, :neighborhood_id)'
        );
        $stmt->execute([
            'name'            => $name,
            'email'           => $email,
            'hash'            => password_hash('changeme123', PASSWORD_DEFAULT),
            'role'            => in_array($role, ['resident', 'admin'], true) ? $role : 'resident',
            'neighborhood_id' => $neighborhoodId,
        ]);

        return (new AuthService())->getUserById((int) $this->db->lastInsertId());
    }

    public function setStatus(int $userId, string $status): array
    {
        if (!in_array($status, ['active', 'suspended'], true)) {
            throw new InvalidArgumentException('Invalid status.');
        }
        $stmt = $this->db->prepare('UPDATE users SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $userId]);
        return (new AuthService())->getUserById($userId);
    }

    public function countByStatus(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) AS suspended,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admins
             FROM users"
        );
        $row = $stmt->fetch();
        return [
            'total'     => (int) $row['total'],
            'active'    => (int) $row['active'],
            'suspended' => (int) $row['suspended'],
            'admins'    => (int) $row['admins'],
        ];
    }
}
