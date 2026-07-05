<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            NeighborhoodSeeder::class,
            UserSeeder::class,
            ReportSeeder::class,
            NotificationSeeder::class,
            SubscriptionSeeder::class,
        ]);
    }
}
