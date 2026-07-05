<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::find(1)->subscriptions()->attach([1, 3]);
    }
}
