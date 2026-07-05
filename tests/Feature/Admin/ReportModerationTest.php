<?php

namespace Tests\Feature\Admin;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_report_endpoints(): void
    {
        $this->getJson('/api/admin/reports')->assertStatus(403);
    }

    public function test_a_resident_cannot_access_admin_report_endpoints(): void
    {
        $resident = $this->createResident();

        $this->actingAs($resident)->getJson('/api/admin/reports')->assertStatus(403);
    }

    public function test_an_admin_can_list_reports(): void
    {
        $admin = $this->createAdmin();
        $neighborhood = $this->createNeighborhood();
        $reporter = $this->createResident();
        Report::create(['user_id' => $reporter->id, 'neighborhood_id' => $neighborhood->id, 'status' => 'available', 'notes' => 'All fine over here today.']);

        $response = $this->actingAs($admin)->getJson('/api/admin/reports');

        $response->assertStatus(200)->assertJsonCount(1, 'reports');
    }

    public function test_an_admin_can_verify_a_report_and_the_reporter_neighborhood_is_notified(): void
    {
        $admin = $this->createAdmin();
        $neighborhood = $this->createNeighborhood();
        $reporter = $this->createResident();
        $subscriber = $this->createResident(['email' => 'sub@example.com']);
        $subscriber->subscriptions()->attach($neighborhood->id);

        $report = Report::create([
            'user_id' => $reporter->id,
            'neighborhood_id' => $neighborhood->id,
            'status' => 'available',
            'notes' => 'All fine over here today.',
        ]);

        $response = $this->actingAs($admin)->putJson("/api/admin/reports/{$report->id}/verify");

        $response->assertStatus(200)->assertJsonPath('report.verified', true);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'verified' => true]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $subscriber->id,
            'neighborhood_id' => $neighborhood->id,
            'type' => 'success',
        ]);
    }

    public function test_a_resident_cannot_verify_a_report(): void
    {
        $resident = $this->createResident();
        $neighborhood = $this->createNeighborhood();
        $report = Report::create([
            'user_id' => $resident->id,
            'neighborhood_id' => $neighborhood->id,
            'status' => 'available',
            'notes' => 'All fine over here today.',
        ]);

        $this->actingAs($resident)->putJson("/api/admin/reports/{$report->id}/verify")->assertStatus(403);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'verified' => false]);
    }

    public function test_an_admin_can_delete_a_report(): void
    {
        $admin = $this->createAdmin();
        $neighborhood = $this->createNeighborhood();
        $reporter = $this->createResident();
        $report = Report::create([
            'user_id' => $reporter->id,
            'neighborhood_id' => $neighborhood->id,
            'status' => 'available',
            'notes' => 'All fine over here today.',
        ]);

        $this->actingAs($admin)->deleteJson("/api/admin/reports/{$report->id}")->assertStatus(200);

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }
}
