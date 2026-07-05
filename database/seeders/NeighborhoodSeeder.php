<?php

namespace Database\Seeders;

use App\Models\Neighborhood;
use Illuminate\Database\Seeder;

class NeighborhoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $neighborhoods = [
            ['Westlands', 'Nairobi West'],
            ['Kilimani', 'Nairobi Central'],
            ['Kasarani', 'Nairobi East'],
            ['Embakasi', 'Nairobi East'],
            ['Karen', 'Nairobi South'],
            ['Ruaraka', 'Nairobi North'],
            ['Pipeline', 'Nairobi East'],
            ['Dandora', 'Nairobi East'],
            ['Kibera', 'Nairobi West'],
            ['Parklands', 'Nairobi North'],
        ];

        foreach ($neighborhoods as [$name, $area]) {
            Neighborhood::create(['name' => $name, 'area' => $area]);
        }
    }
}
