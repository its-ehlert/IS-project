<?php

declare(strict_types=1);

class NotificationService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return array_map('formatNotificationRow', $stmt->fetchAll());
    }

    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0'
        );
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAsRead(int $userId, int $notificationId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
    }

    public function markAllAsRead(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
    }

    public function notifySubscribers(int $neighborhoodId, string $title, string $message, string $type = 'info'): void
    {
        $stmt = $this->db->prepare(
            'SELECT user_id FROM subscriptions WHERE neighborhood_id = :neighborhood_id'
        );
        $stmt->execute(['neighborhood_id' => $neighborhoodId]);
        $subscribers = $stmt->fetchAll();

        $insert = $this->db->prepare(
            'INSERT INTO notifications (user_id, neighborhood_id, type, title, message)
             VALUES (:user_id, :neighborhood_id, :type, :title, :message)'
        );

        foreach ($subscribers as $row) {
            $insert->execute([
                'user_id'          => (int) $row['user_id'],
                'neighborhood_id'  => $neighborhoodId,
                'type'             => $type,
                'title'            => $title,
                'message'          => $message,
            ]);
        }
    }

    public function notifyReportSubmitted(int $neighborhoodId, string $neighborhoodName, string $status, int $excludeUserId): void
    {
        $type = statusNotificationType($status);
        $label = statusLabel($status);
        $this->notifySubscribers(
            $neighborhoodId,
            "New report: {$neighborhoodName}",
            "A community member reported {$label} in {$neighborhoodName}.",
            $type
        );
    }

    public function notifyReportVerified(int $neighborhoodId, string $neighborhoodName, string $status): void
    {
        $type = statusNotificationType($status);
        $label = statusLabel($status);
        $this->notifySubscribers(
            $neighborhoodId,
            "Verified update: {$neighborhoodName}",
            "An administrator verified a {$label} report for {$neighborhoodName}.",
            $type
        );
    }

    public function getSubscriptions(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT neighborhood_id FROM subscriptions WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        return array_map(fn($r) => (int) $r['neighborhood_id'], $stmt->fetchAll());
    }

    public function saveSubscriptions(int $userId, array $neighborhoodIds): void
    {
        $this->db->prepare('DELETE FROM subscriptions WHERE user_id = :user_id')
            ->execute(['user_id' => $userId]);

        $stmt = $this->db->prepare(
            'INSERT INTO subscriptions (user_id, neighborhood_id) VALUES (:user_id, :neighborhood_id)'
        );
        foreach ($neighborhoodIds as $id) {
            $stmt->execute(['user_id' => $userId, 'neighborhood_id' => (int) $id]);
        }
    }
}
