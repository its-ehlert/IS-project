<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_resident_cannot_access_admin_user_endpoints(): void
    {
        $resident = $this->createResident();

        $this->actingAs($resident)->getJson('/api/admin/users')->assertStatus(403);
    }

    public function test_an_admin_can_list_users_with_status_counts(): void
    {
        $admin = $this->createAdmin();
        $this->createResident(['status' => 'suspended']);

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonPath('stats.total', 2)
            ->assertJsonPath('stats.suspended', 1)
            ->assertJsonPath('stats.admins', 1);
    }

    public function test_an_admin_can_create_a_user(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson('/api/admin/users', [
            'name' => 'Created By Admin',
            'email' => 'created@example.com',
            'role' => 'resident',
        ]);

        $response->assertStatus(201)->assertJsonPath('user.email', 'created@example.com');
        $this->assertDatabaseHas('users', ['email' => 'created@example.com', 'role' => 'resident']);
    }

    public function test_creating_a_user_requires_a_name_and_email(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson('/api/admin/users', ['name' => '', 'email' => '']);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_an_admin_can_suspend_and_reactivate_a_user(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createResident(['status' => 'active']);

        $this->actingAs($admin)->putJson("/api/admin/users/{$target->id}/suspend")
            ->assertStatus(200)
            ->assertJsonPath('user.status', 'suspended');
        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'suspended']);

        $this->actingAs($admin)->putJson("/api/admin/users/{$target->id}/activate")
            ->assertStatus(200)
            ->assertJsonPath('user.status', 'active');
        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'active']);
    }

    public function test_a_suspended_user_cannot_log_in(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createResident([
            'email' => 'target@example.com',
            'password_hash' => bcrypt('password123'),
        ]);

        $this->actingAs($admin)->putJson("/api/admin/users/{$target->id}/suspend")->assertStatus(200);

        $this->postJson('/api/auth/login', [
            'email' => 'target@example.com',
            'password' => 'password123',
        ])->assertStatus(409);
    }
}
