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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    requireAdmin();

    $dashboard = new DashboardService();
    $reportService = new ReportService();
    $userService = new UserService();

    $reports = $reportService->getAll();
    $pending = array_filter($reports, fn($r) => !$r['verified']);
    $userStats = $userService->countByStatus();
    $activity = $dashboard->getActivityByDay();

    jsonResponse([
        'success'        => true,
        'stats'          => [
            'totalReports'    => count($reports),
            'pendingReports'  => count($pending),
            'totalUsers'      => $userStats['total'],
            'activeUsers'     => $userStats['active'],
            'reportsToday'    => $reportService->countToday(),
        ],
        'pendingReports' => array_slice(array_values($pending), 0, 5),
        'recentUsers'    => array_slice($userService->getAll(), 0, 5),
        'activity'       => $activity,
        'events'         => [
            ['time' => date('c'), 'level' => 'info', 'component' => 'System', 'message' => 'Admin dashboard loaded'],
            ['time' => date('c', strtotime('-1 hour')), 'level' => 'info', 'component' => 'Reports', 'message' => count($reports) . ' total reports in database'],
            ['time' => date('c', strtotime('-2 hours')), 'level' => 'success', 'component' => 'Database', 'message' => 'MySQL connection operational'],
        ],
    ]);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
