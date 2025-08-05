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

        // Initialize default settings if they don't exist
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

        $this->sessions = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'is_current_device' => $session->id === session()->getId(),
                    'device' => Str::limit($session->user_agent, 40),
                ];
            });
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