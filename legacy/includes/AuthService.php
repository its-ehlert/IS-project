<?php

declare(strict_types=1);

class AuthService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function register(array $data): array
    {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $neighborhoodId = isset($data['neighborhoodId']) ? (int) $data['neighborhoodId'] : null;

        if ($name === '' || $email === '' || strlen($password) < 8) {
            throw new InvalidArgumentException('Name, email, and password (min 8 chars) are required.');
        }

        if ($this->emailExists($email)) {
            throw new RuntimeException('An account with this email already exists.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role, neighborhood_id)
             VALUES (:name, :email, :hash, :role, :neighborhood_id)'
        );
        $stmt->execute([
            'name'            => $name,
            'email'           => $email,
            'hash'            => password_hash($password, PASSWORD_DEFAULT),
            'role'            => 'resident',
            'neighborhood_id' => $neighborhoodId ?: null,
        ]);

        $userId = (int) $this->db->lastInsertId();
        if ($neighborhoodId) {
            $this->subscribe($userId, $neighborhoodId);
        }

        $this->createWelcomeNotification($userId);

        return $this->getUserById($userId);
    }

    public function authenticate(string $email, string $password): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, n.name AS neighborhood_name
             FROM users u
             LEFT JOIN neighborhoods n ON n.id = u.neighborhood_id
             WHERE u.email = :email LIMIT 1'
        );
        $stmt->execute(['email' => trim($email)]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, $row['password_hash'])) {
            return null;
        }

        if ($row['status'] === 'suspended') {
            throw new RuntimeException('Your account has been suspended. Contact an administrator.');
        }

        return formatUserRow($row);
    }

    public function getUserById(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, n.name AS neighborhood_name
             FROM users u
             LEFT JOIN neighborhoods n ON n.id = u.neighborhood_id
             WHERE u.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new RuntimeException('User not found.');
        }
        return formatUserRow($row);
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetch();
    }

    private function subscribe(int $userId, int $neighborhoodId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO subscriptions (user_id, neighborhood_id) VALUES (:user_id, :neighborhood_id)'
        );
        $stmt->execute(['user_id' => $userId, 'neighborhood_id' => $neighborhoodId]);
    }

    private function createWelcomeNotification(int $userId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, message)
             VALUES (:user_id, :type, :title, :message)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'type'    => 'info',
            'title'   => 'Welcome to AquaWatch Nairobi',
            'message' => 'Start by reporting water status in your neighbourhood or subscribing to alerts.',
        ]);
    }
}
