<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\DashboardStats;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardStatsNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    protected function createNotification(User $user, bool $read = false): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'message' => 'Test Notification Message',
                'url' => '/dashboard',
            ],
            'read_at' => $read ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_notifications_load_properly(): void
    {
        $this->actingAs($this->user);

        $n1 = $this->createNotification($this->user, false);
        $n2 = $this->createNotification($this->user, true);

        $component = Livewire::test(DashboardStats::class);

        $this->assertEquals(1, $component->get('unreadCount'));
        $this->assertCount(2, $component->get('notifications'));
    }

    public function test_mark_as_read_executes_rapidly_and_updates_db(): void
    {
        $this->actingAs($this->user);

        $notif = $this->createNotification($this->user, false);

        $component = Livewire::test(DashboardStats::class)
            ->call('markAsRead', $notif->id);

        $this->assertNotNull($notif->fresh()->read_at);
        $this->assertEquals(0, $component->get('unreadCount'));
    }

    public function test_mark_all_as_read_executes_and_clears_unread(): void
    {
        $this->actingAs($this->user);

        $n1 = $this->createNotification($this->user, false);
        $n2 = $this->createNotification($this->user, false);

        $component = Livewire::test(DashboardStats::class)
            ->call('markAllAsRead');

        $this->assertNotNull($n1->fresh()->read_at);
        $this->assertNotNull($n2->fresh()->read_at);
        $this->assertEquals(0, $component->get('unreadCount'));
    }

    public function test_delete_notification_removes_single_record(): void
    {
        $this->actingAs($this->user);

        $n1 = $this->createNotification($this->user, false);
        $n2 = $this->createNotification($this->user, false);

        $component = Livewire::test(DashboardStats::class)
            ->call('deleteNotification', $n1->id);

        $this->assertNull(DatabaseNotification::find($n1->id));
        $this->assertNotNull(DatabaseNotification::find($n2->id));
        $this->assertEquals(1, $component->get('unreadCount'));
    }

    public function test_delete_all_notifications_clears_entire_list(): void
    {
        $this->actingAs($this->user);

        $this->createNotification($this->user, false);
        $this->createNotification($this->user, true);

        $component = Livewire::test(DashboardStats::class)
            ->call('deleteAllNotifications');

        $this->assertEquals(0, $this->user->notifications()->count());
        $this->assertEquals(0, $component->get('unreadCount'));
        $this->assertCount(0, $component->get('notifications'));
    }

    public function test_blade_view_contains_optimistic_handlers_and_preserves_alpine_state(): void
    {
        $content = file_get_contents(resource_path('views/livewire/dashboard/dashboard-stats.blade.php'));

        // Preserves benchmark Alpine contract
        $this->assertStringContainsString('x-data="{ open: false }"', $content);
        $this->assertMatchesRegularExpression('/<button[^>]*@click="open\s*=\s*!\s*open"[^>]*>/', $content);

        // Contains optimistic dismiss and clear handlers
        $this->assertStringContainsString('clearedAll', $content);
        $this->assertStringContainsString('clear()', $content);
        $this->assertStringContainsString('remainingCount', $content);
    }
}
