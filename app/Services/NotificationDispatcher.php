<?php

namespace App\Services;

use App\Models\Neighborhood;
use App\Models\Notification;

/**
 * Fans notifications out to every resident subscribed to a neighbourhood.
 * Kept as a small service (rather than folded into the Notification model)
 * because it orchestrates across Neighborhood's subscribers relation.
 */
class NotificationDispatcher
{
    public function notifySubscribers(int $neighborhoodId, string $title, string $message, string $type = 'info'): void
    {
        $neighborhood = Neighborhood::find($neighborhoodId);
        if (! $neighborhood) {
            return;
        }

        foreach ($neighborhood->subscribers()->pluck('users.id') as $userId) {
            Notification::forceCreate([
                'user_id' => $userId,
                'neighborhood_id' => $neighborhoodId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
            ]);
        }
    }

    public function notifyReportSubmitted(int $neighborhoodId, string $neighborhoodName, string $status): void
    {
        $this->notifySubscribers(
            $neighborhoodId,
            "New report: {$neighborhoodName}",
            "A community member reported {$this->statusLabel($status)} in {$neighborhoodName}.",
            $this->statusNotificationType($status)
        );
    }

    public function notifyReportVerified(int $neighborhoodId, string $neighborhoodName, string $status): void
    {
        $this->notifySubscribers(
            $neighborhoodId,
            "Verified update: {$neighborhoodName}",
            "An administrator verified a {$this->statusLabel($status)} report for {$neighborhoodName}.",
            $this->statusNotificationType($status)
        );
    }

    public function statusNotificationType(string $status): string
    {
        return match ($status) {
            'available' => 'success',
            'low' => 'warning',
            'none' => 'danger',
            'scheduled' => 'info',
            default => 'info',
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'available' => 'Available',
            'low' => 'Low Pressure',
            'none' => 'No Water',
            'scheduled' => 'Scheduled',
            default => ucfirst($status),
        };
    }
}
