<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoHash = Hash::make('demo123');
        $adminHash = Hash::make('admin123');

        $users = [
            ['Demo User', 'demo@aquawatch.ke', $demoHash, 'resident', 'active', 1],
            ['Jane Mwangi', 'jane.m@email.com', $demoHash, 'resident', 'active', 1],
            ['Peter Ochieng', 'peter.o@email.com', $demoHash, 'resident', 'active', 3],
            ['Grace Wanjiku', 'grace.w@email.com', $demoHash, 'resident', 'active', 2],
            ['Admin User', 'admin@aquawatch.ke', $adminHash, 'admin', 'active', null],
            ['Suspended User', 'suspended@email.com', $demoHash, 'resident', 'suspended', 8],
        ];

        foreach ($users as [$name, $email, $hash, $role, $status, $neighborhoodId]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password_hash' => $hash,
                'role' => $role,
                'status' => $status,
                'neighborhood_id' => $neighborhoodId,
            ]);
        }
    }
}
