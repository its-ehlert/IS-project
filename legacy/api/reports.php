<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

startSession();

$method = $_SERVER['REQUEST_METHOD'];
$reportService = new ReportService();
$notificationService = new NotificationService();

function requireLogin(): int
{
    if (empty($_SESSION['user_id'])) {
        jsonError('Authentication required.', 401);
    }
    return (int) $_SESSION['user_id'];
}

try {
    if ($method === 'GET') {
        $filters = [
            'neighborhoodId' => $_GET['neighborhoodId'] ?? '',
            'status'         => $_GET['status'] ?? '',
            'search'         => $_GET['search'] ?? '',
            'verified'       => $_GET['verified'] ?? '',
            'limit'          => $_GET['limit'] ?? '',
        ];
        $reports = $reportService->getAll($filters);
        jsonResponse(['success' => true, 'reports' => $reports]);
    }

    if ($method === 'POST') {
        $userId = requireLogin();
        $input = getJsonInput();
        $report = $reportService->create($userId, $input);

        $notificationService->notifyReportSubmitted(
            $report['neighborhoodId'],
            $report['neighborhood'],
            $report['status'],
            $userId
        );

        jsonResponse(['success' => true, 'report' => $report], 201);
    }

    jsonError('Method not allowed', 405);
} catch (InvalidArgumentException $e) {
    jsonError($e->getMessage(), 422);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
