<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_submit_a_report(): void
    {
        $neighborhood = $this->createNeighborhood();

        $response = $this->postJson('/api/reports', [
            'neighborhoodId' => $neighborhood->id,
            'status' => 'available',
            'notes' => 'Plenty of pressure this morning.',
        ]);

        $response->assertStatus(401)->assertJsonPath('success', false);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_a_resident_can_submit_a_valid_report(): void
    {
        $neighborhood = $this->createNeighborhood();
        $user = $this->createResident(['neighborhood_id' => $neighborhood->id]);

        $response = $this->actingAs($user)->postJson('/api/reports', [
            'neighborhoodId' => $neighborhood->id,
            'status' => 'low',
            'notes' => 'Pressure has been low since this morning.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('report.status', 'low')
            ->assertJsonPath('report.neighborhoodId', $neighborhood->id)
            ->assertJsonPath('report.verified', false);

        $this->assertDatabaseHas('reports', [
            'user_id' => $user->id,
            'neighborhood_id' => $neighborhood->id,
            'status' => 'low',
        ]);
    }

    public function test_report_submission_rejects_notes_under_ten_characters(): void
    {
        $neighborhood = $this->createNeighborhood();
        $user = $this->createResident();

        $response = $this->actingAs($user)->postJson('/api/reports', [
            'neighborhoodId' => $neighborhood->id,
            'status' => 'available',
            'notes' => 'too short',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_submission_rejects_an_invalid_status(): void
    {
        $neighborhood = $this->createNeighborhood();
        $user = $this->createResident();

        $response = $this->actingAs($user)->postJson('/api/reports', [
            'neighborhoodId' => $neighborhood->id,
            'status' => 'not-a-real-status',
            'notes' => 'This status value does not exist.',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_submission_rejects_a_missing_neighborhood(): void
    {
        $user = $this->createResident();

        $response = $this->actingAs($user)->postJson('/api/reports', [
            'neighborhoodId' => 0,
            'status' => 'available',
            'notes' => 'No neighborhood id was provided here.',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_submitting_a_report_notifies_subscribed_residents_but_not_the_reporter(): void
    {
        $neighborhood = $this->createNeighborhood();
        $reporter = $this->createResident(['email' => 'reporter@example.com']);
        $subscriber = $this->createResident(['email' => 'subscriber@example.com']);
        $subscriber->subscriptions()->attach($neighborhood->id);

        $this->actingAs($reporter)->postJson('/api/reports', [
            'neighborhoodId' => $neighborhood->id,
            'status' => 'none',
            'notes' => 'No water at all since yesterday evening.',
        ])->assertStatus(201);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $subscriber->id,
            'neighborhood_id' => $neighborhood->id,
            'type' => 'danger',
        ]);
        $this->assertSame(0, Notification::where('user_id', $reporter->id)->count());
    }

    public function test_index_filters_reports_by_neighborhood(): void
    {
        $wanted = $this->createNeighborhood(['name' => 'Kilimani']);
        $other = $this->createNeighborhood(['name' => 'Karen']);
        $user = $this->createResident();

        Report::create(['user_id' => $user->id, 'neighborhood_id' => $wanted->id, 'status' => 'available', 'notes' => 'All good here today.']);
        Report::create(['user_id' => $user->id, 'neighborhood_id' => $other->id, 'status' => 'none', 'notes' => 'Nothing coming out at all.']);

        $response = $this->getJson('/api/reports?neighborhoodId='.$wanted->id);

        $response->assertStatus(200)->assertJsonCount(1, 'reports');
        $response->assertJsonPath('reports.0.neighborhoodId', $wanted->id);
    }

    public function test_index_filters_reports_by_verified_status(): void
    {
        $neighborhood = $this->createNeighborhood();
        $user = $this->createResident();

        $verified = Report::create(['user_id' => $user->id, 'neighborhood_id' => $neighborhood->id, 'status' => 'available', 'notes' => 'Verified report right here.']);
        $verified->verified = true;
        $verified->save();
        Report::create(['user_id' => $user->id, 'neighborhood_id' => $neighborhood->id, 'status' => 'available', 'notes' => 'Unverified report right here.']);

        $response = $this->getJson('/api/reports?verified=true');

        $response->assertStatus(200)->assertJsonCount(1, 'reports');
        $response->assertJsonPath('reports.0.verified', true);
    }
}
