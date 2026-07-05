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
$reportService = new ReportService();
$notificationService = new NotificationService();

try {
    requireAdmin();

    if ($method === 'GET') {
        $filters = [
            'neighborhoodId' => $_GET['neighborhoodId'] ?? '',
            'search'         => $_GET['search'] ?? '',
            'verified'       => $_GET['verified'] ?? '',
        ];
        if (isset($_GET['verifiedFilter'])) {
            $vf = $_GET['verifiedFilter'];
            if ($vf === 'verified') $filters['verified'] = '1';
            if ($vf === 'unverified') $filters['verified'] = '0';
        }
        $reports = $reportService->getAll($filters);
        jsonResponse(['success' => true, 'reports' => $reports]);
    }

    if ($method === 'PUT') {
        $input = getJsonInput();
        $reportId = (int) ($input['id'] ?? 0);
        $action = $input['action'] ?? '';

        if ($action === 'verify' && $reportId) {
            $before = $reportService->getById($reportId);
            $report = $reportService->verify($reportId);
            $notificationService->notifyReportVerified(
                $report['neighborhoodId'],
                $report['neighborhood'],
                $report['status']
            );
            jsonResponse(['success' => true, 'report' => $report]);
        }

        jsonError('Invalid action.', 400);
    }

    if ($method === 'DELETE') {
        $reportId = (int) ($_GET['id'] ?? 0);
        if (!$reportId) {
            jsonError('Report ID required.', 422);
        }
        $reportService->delete($reportId);
        jsonResponse(['success' => true]);
    }

    jsonError('Method not allowed', 405);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
