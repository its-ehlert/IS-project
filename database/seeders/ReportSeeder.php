<?php

namespace Database\Seeders;

use App\Models\Report;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reports = [
            [2, 1, 'available', 'Strong flow since 6 AM. Tank is filling normally.', 1],
            [3, 3, 'none', 'No water since yesterday evening. Multiple households affected on Mwiki road.', 1],
            [4, 2, 'low', 'Trickle only. Pressure very low on 4th floor.', 0],
            [2, 4, 'scheduled', 'NCWSC announced supply restoration between 2–6 PM today.', 1],
            [3, 7, 'none', 'Burst pipe reported near stage. No supply in the area.', 1],
            [2, 5, 'available', 'Normal supply. No issues reported.', 1],
            [4, 9, 'low', 'Water available but only for 2 hours in the morning.', 0],
            [3, 6, 'available', 'Good pressure throughout the morning.', 1],
        ];
        $hoursAgo = [2, 4, 5, 18, 30, 36, 48, 60];

        foreach ($reports as $i => [$userId, $neighborhoodId, $status, $notes, $verified]) {
            Report::forceCreate([
                'user_id' => $userId,
                'neighborhood_id' => $neighborhoodId,
                'status' => $status,
                'notes' => $notes,
                'verified' => $verified,
                'reported_at' => now()->subHours($hoursAgo[$i]),
            ]);
        }
    }
}
