<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/ReportService.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/DashboardService.php';
require_once __DIR__ . '/UserService.php';

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $code = 400): void
{
    jsonResponse(['success' => false, 'error' => $message], $code);
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function requireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        jsonError('Method not allowed', 405);
    }
}

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function formatUserRow(array $row): array
{
    return [
        'id'             => (int) $row['id'],
        'name'           => $row['name'],
        'email'          => $row['email'],
        'role'           => $row['role'],
        'status'         => $row['status'],
        'neighborhoodId' => $row['neighborhood_id'] ? (int) $row['neighborhood_id'] : null,
        'neighborhood'   => $row['neighborhood_name'] ?? null,
        'joinedAt'       => substr($row['created_at'], 0, 10),
    ];
}

function formatReportRow(array $row): array
{
    return [
        'id'             => (int) $row['id'],
        'userId'         => (int) $row['user_id'],
        'userName'       => $row['user_name'],
        'neighborhoodId' => (int) $row['neighborhood_id'],
        'neighborhood'   => $row['neighborhood_name'],
        'status'         => $row['status'],
        'notes'          => $row['notes'],
        'reportedAt'     => date('c', strtotime($row['reported_at'])),
        'verified'       => (bool) $row['verified'],
    ];
}

function formatNotificationRow(array $row): array
{
    return [
        'id'             => (int) $row['id'],
        'userId'         => (int) $row['user_id'],
        'neighborhoodId' => $row['neighborhood_id'] ? (int) $row['neighborhood_id'] : null,
        'type'           => $row['type'],
        'title'          => $row['title'],
        'message'        => $row['message'],
        'read'           => (bool) $row['is_read'],
        'createdAt'      => date('c', strtotime($row['created_at'])),
    ];
}

function statusNotificationType(string $status): string
{
    return match ($status) {
        'available'  => 'success',
        'low'        => 'warning',
        'none'       => 'danger',
        'scheduled'  => 'info',
        default      => 'info',
    };
}

function statusLabel(string $status): string
{
    return match ($status) {
        'available' => 'Available',
        'low'       => 'Low Pressure',
        'none'      => 'No Water',
        'scheduled' => 'Scheduled',
        default     => ucfirst($status),
    };
}
