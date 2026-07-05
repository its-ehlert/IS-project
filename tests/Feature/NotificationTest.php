<?php

namespace Tests\Feature;

use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_notifications(): void
    {
        $this->getJson('/api/notifications')->assertStatus(401);
    }

    public function test_a_user_only_sees_their_own_notifications(): void
    {
        $user = $this->createResident();
        $otherUser = $this->createResident(['email' => 'other@example.com']);

        Notification::forceCreate(['user_id' => $user->id, 'type' => 'info', 'title' => 'Mine', 'message' => 'For me.']);
        Notification::forceCreate(['user_id' => $otherUser->id, 'type' => 'info', 'title' => 'Not mine', 'message' => 'For them.']);

        $response = $this->actingAs($user)->getJson('/api/notifications');

        $response->assertStatus(200)->assertJsonCount(1, 'notifications');
        $response->assertJsonPath('notifications.0.title', 'Mine');
    }

    public function test_unread_count_only_counts_unread_notifications(): void
    {
        $user = $this->createResident();
        Notification::forceCreate(['user_id' => $user->id, 'type' => 'info', 'title' => 'Unread', 'message' => 'x', 'is_read' => false]);
        Notification::forceCreate(['user_id' => $user->id, 'type' => 'info', 'title' => 'Read', 'message' => 'x', 'is_read' => true]);

        $response = $this->actingAs($user)->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)->assertJsonPath('count', 1);
    }

    public function test_mark_all_read_updates_every_notification_for_the_user(): void
    {
        $user = $this->createResident();
        Notification::forceCreate(['user_id' => $user->id, 'type' => 'info', 'title' => 'A', 'message' => 'x']);
        Notification::forceCreate(['user_id' => $user->id, 'type' => 'info', 'title' => 'B', 'message' => 'x']);

        $this->actingAs($user)->putJson('/api/notifications/mark-all-read')->assertStatus(200);

        $this->assertSame(0, Notification::where('user_id', $user->id)->where('is_read', false)->count());
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = $this->createResident();
        $otherUser = $this->createResident(['email' => 'other@example.com']);
        $notification = Notification::forceCreate(['user_id' => $otherUser->id, 'type' => 'info', 'title' => 'Not mine', 'message' => 'x']);

        $this->actingAs($user)->putJson("/api/notifications/{$notification->id}/read")->assertStatus(200);

        $this->assertFalse($notification->fresh()->is_read);
    }

    public function test_saving_subscriptions_replaces_the_previous_set(): void
    {
        $user = $this->createResident();
        $first = $this->createNeighborhood(['name' => 'Westlands']);
        $second = $this->createNeighborhood(['name' => 'Karen']);
        $user->subscriptions()->attach($first->id);

        $response = $this->actingAs($user)->putJson('/api/notifications/subscriptions', [
            'neighborhoodIds' => [$second->id],
        ]);

        $response->assertStatus(200);
        $ids = $user->subscriptions()->pluck('neighborhoods.id')->all();
        $this->assertSame([$second->id], $ids);
    }
}
