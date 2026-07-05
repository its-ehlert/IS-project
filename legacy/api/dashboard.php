<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    $dashboard = new DashboardService();
    $neighborhoodId = isset($_GET['neighborhoodId']) && $_GET['neighborhoodId'] !== ''
        ? (int) $_GET['neighborhoodId'] : null;

    jsonResponse([
        'success'              => true,
        'stats'                => $dashboard->getStats(),
        'weeklyTrends'         => $dashboard->getWeeklyTrends($neighborhoodId),
        'monthlyStats'         => $dashboard->getMonthlyStats(),
        'neighborhoodSummary'  => $dashboard->getNeighborhoodSummary(),
    ]);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
