<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

startSession();

$method = $_SERVER['REQUEST_METHOD'];
$notificationService = new NotificationService();

function requireLogin(): int
{
    if (empty($_SESSION['user_id'])) {
        jsonError('Authentication required.', 401);
    }
    return (int) $_SESSION['user_id'];
}

try {
    $userId = requireLogin();

    if ($method === 'GET' && ($_GET['action'] ?? '') === 'unread-count') {
        $count = $notificationService->getUnreadCount($userId);
        jsonResponse(['success' => true, 'count' => $count]);
    }

    if ($method === 'GET' && ($_GET['action'] ?? '') === 'subscriptions') {
        $subs = $notificationService->getSubscriptions($userId);
        jsonResponse(['success' => true, 'subscriptions' => $subs]);
    }

    if ($method === 'GET') {
        $notifications = $notificationService->getForUser($userId);
        jsonResponse(['success' => true, 'notifications' => $notifications]);
    }

    if ($method === 'PUT') {
        $input = getJsonInput();
        $action = $input['action'] ?? '';

        if ($action === 'mark-all-read') {
            $notificationService->markAllAsRead($userId);
            jsonResponse(['success' => true]);
        }

        if ($action === 'mark-read' && !empty($input['id'])) {
            $notificationService->markAsRead($userId, (int) $input['id']);
            jsonResponse(['success' => true]);
        }

        if ($action === 'save-subscriptions') {
            $ids = array_map('intval', $input['neighborhoodIds'] ?? []);
            $notificationService->saveSubscriptions($userId, $ids);
            jsonResponse(['success' => true, 'subscriptions' => $ids]);
        }

        jsonError('Invalid action.', 400);
    }

    jsonError('Method not allowed', 405);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
