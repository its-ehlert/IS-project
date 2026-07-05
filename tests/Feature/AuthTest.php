<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_a_resident_and_logs_them_in(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Resident',
            'email' => 'new.resident@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.email', 'new.resident@example.com')
            ->assertJsonPath('user.role', 'resident');

        $this->assertDatabaseHas('users', ['email' => 'new.resident@example.com']);
        $this->assertAuthenticated();
    }

    public function test_register_rejects_a_password_under_eight_characters(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Resident',
            'email' => 'short.pass@example.com',
            'password' => 'short',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
        $this->assertDatabaseMissing('users', ['email' => 'short.pass@example.com']);
    }

    public function test_register_rejects_a_duplicate_email(): void
    {
        $this->createResident(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Someone Else',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(409)->assertJsonPath('success', false);
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $this->createResident([
            'email' => 'login@example.com',
            'password_hash' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertStatus(200)->assertJsonPath('user.email', 'login@example.com');
        $this->assertAuthenticated();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createResident([
            'email' => 'login@example.com',
            'password_hash' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)->assertJsonPath('success', false);
        $this->assertGuest();
    }

    public function test_login_blocks_a_suspended_account(): void
    {
        $this->createResident([
            'email' => 'suspended@example.com',
            'password_hash' => bcrypt('correct-password'),
            'status' => 'suspended',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'suspended@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertStatus(409)->assertJsonPath('success', false);
        $this->assertGuest();
    }

    public function test_me_returns_null_when_guest(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)->assertJsonPath('user', null);
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = $this->createResident(['email' => 'me@example.com']);

        $response = $this->actingAs($user)->getJson('/api/auth/me');

        $response->assertStatus(200)->assertJsonPath('user.email', 'me@example.com');
    }

    public function test_logout_ends_the_session(): void
    {
        $user = $this->createResident();

        $this->actingAs($user)->postJson('/api/auth/logout')->assertStatus(200);

        $this->assertGuest();
    }
}
