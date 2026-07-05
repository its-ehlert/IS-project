<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

startSession();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$auth = new AuthService();

try {
    if ($method === 'POST' && $action === 'login') {
        $input = getJsonInput();
        $user = $auth->authenticate($input['email'] ?? '', $input['password'] ?? '');
        if (!$user) {
            jsonError('Invalid email or password.', 401);
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        jsonResponse(['success' => true, 'user' => $user]);
    }

    if ($method === 'POST' && $action === 'register') {
        $input = getJsonInput();
        $user = $auth->register($input);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        jsonResponse(['success' => true, 'user' => $user], 201);
    }

    if ($method === 'POST' && $action === 'logout') {
        session_destroy();
        jsonResponse(['success' => true]);
    }

    if ($method === 'GET' && $action === 'me') {
        if (empty($_SESSION['user_id'])) {
            jsonResponse(['success' => true, 'user' => null]);
        }
        $user = $auth->getUserById((int) $_SESSION['user_id']);
        jsonResponse(['success' => true, 'user' => $user]);
    }

    jsonError('Invalid action.', 400);
} catch (InvalidArgumentException $e) {
    jsonError($e->getMessage(), 422);
} catch (RuntimeException $e) {
    jsonError($e->getMessage(), 409);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
