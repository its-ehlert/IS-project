<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

startSession();

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = Database::connect()->query('SELECT id, name, area FROM neighborhoods ORDER BY name');
        $rows = array_map(fn($r) => [
            'id'   => (int) $r['id'],
            'name' => $r['name'],
            'area' => $r['area'],
        ], $stmt->fetchAll());
        jsonResponse(['success' => true, 'neighborhoods' => $rows]);
    }

    jsonError('Method not allowed', 405);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
