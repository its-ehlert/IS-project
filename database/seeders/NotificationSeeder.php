<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            [1, 3, 'warning', 'No water in Kasarani', '3 new reports confirm no supply in Kasarani since yesterday.', 0, 3],
            [1, 1, 'success', 'Supply restored in Westlands', 'Community reports indicate normal water flow in Westlands.', 0, 2],
            [1, 4, 'info', 'Scheduled maintenance', 'Embakasi: NCWSC announced supply between 2–6 PM today.', 1, 20],
            [1, null, 'info', 'Welcome to AquaWatch Nairobi', 'Start by reporting water status in your neighbourhood.', 1, 100],
        ];

        foreach ($notifications as [$userId, $neighborhoodId, $type, $title, $message, $isRead, $hoursAgo]) {
            Notification::forceCreate([
                'user_id' => $userId,
                'neighborhood_id' => $neighborhoodId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'is_read' => $isRead,
                'created_at' => now()->subHours($hoursAgo),
            ]);
        }
    }
}
