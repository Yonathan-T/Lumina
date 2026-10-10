<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class General extends Component
{
    public $sessions = [];
    public $dailyReminder = false;
    public $streakAlerts = false;
    public $blogUpdates = false;

    public function mount()
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];

        if (empty($settings)) {
            $settings = [
                'daily_reminder' => false,
                'streak_alerts' => false,
                'blog_updates' => false
            ];
            $user->update(['settings' => $settings]);
        }

        $this->dailyReminder = $settings['daily_reminder'] ?? false;
        $this->streakAlerts = $settings['streak_alerts'] ?? false;
        $this->blogUpdates = $settings['blog_updates'] ?? false;

        try {
            $this->sessions = DB::table('sessions')
                ->where('user_id', auth()->id())
                ->get()
                ->map(function ($session) {
                    $lastActive = !empty($session->last_activity)
                        ? Carbon::createFromTimestamp((int) $session->last_activity)->diffForHumans()
                        : 'Recently';

                    return [
                        'id' => $session->id,
                        'ip_address' => $session->ip_address ?? 'Unknown IP',
                        'user_agent' => $session->user_agent ?? 'Current Device',
                        'last_active' => $lastActive,
                        'is_current_device' => $session->id === session()->getId(),
                        'device' => Str::limit($session->user_agent ?? 'Current Device', 40),
                    ];
                });
        } catch (\Throwable $e) {
            \Log::warning('Failed fetching sessions in settings: ' . $e->getMessage());
            $this->sessions = collect();
        }
    }

    // These methods are triggered automatically when the properties are updated via wire:model
    public function updatedDailyReminder($value)
    {
        $this->updateSetting('daily_reminder', $value);
    }

    public function updatedStreakAlerts($value)
    {
        $this->updateSetting('streak_alerts', $value);
    }

    public function updatedBlogUpdates($value)
    {
        $this->updateSetting('blog_updates', $value);
    }

    private function updateSetting($key, $value)
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings[$key] = $value;
        $user->update(['settings' => $settings]);
    }

    public function logoutSession($sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->delete();
        $this->mount();
    }

    public function render()
    {
        return view('livewire.settings.general');
    }
}