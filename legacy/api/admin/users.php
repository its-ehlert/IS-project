<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

startSession();

function requireAdmin(): void
{
    if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
        jsonError('Admin access required.', 403);
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$userService = new UserService();

try {
    requireAdmin();

    if ($method === 'GET') {
        $filters = [
            'role'   => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];
        $users = $userService->getAll($filters);
        $stats = $userService->countByStatus();
        jsonResponse(['success' => true, 'users' => $users, 'stats' => $stats]);
    }

    if ($method === 'POST') {
        $input = getJsonInput();
        $user = $userService->create($input);
        jsonResponse(['success' => true, 'user' => $user], 201);
    }

    if ($method === 'PUT') {
        $input = getJsonInput();
        $userId = (int) ($input['id'] ?? 0);
        $action = $input['action'] ?? '';

        if ($userId && $action === 'suspend') {
            $user = $userService->setStatus($userId, 'suspended');
            jsonResponse(['success' => true, 'user' => $user]);
        }
        if ($userId && $action === 'activate') {
            $user = $userService->setStatus($userId, 'active');
            jsonResponse(['success' => true, 'user' => $user]);
        }

        jsonError('Invalid action.', 400);
    }

    jsonError('Method not allowed', 405);
} catch (InvalidArgumentException $e) {
    jsonError($e->getMessage(), 422);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
