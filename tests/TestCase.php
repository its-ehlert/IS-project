<?php

namespace Tests;

use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected function createNeighborhood(array $overrides = []): Neighborhood
    {
        return Neighborhood::create(array_merge([
            'name' => 'Westlands',
            'area' => 'Nairobi West',
        ], $overrides));
    }

    protected function createResident(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Jane Resident',
            'email' => 'jane+'.uniqid().'@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'resident',
            'status' => 'active',
        ], $overrides));
    }

    protected function createAdmin(array $overrides = []): User
    {
        return $this->createResident(array_merge([
            'name' => 'Admin User',
            'email' => 'admin+'.uniqid().'@example.com',
            'role' => 'admin',
        ], $overrides));
    }
}
