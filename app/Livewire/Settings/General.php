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
            try {
                $user->update(['settings' => $settings]);
            } catch (\Throwable $e) {
                \Log::warning('Failed initializing user settings: ' . $e->getMessage());
            }
        }

        $this->dailyReminder = $settings['daily_reminder'] ?? false;
        $this->streakAlerts = $settings['streak_alerts'] ?? false;
        $this->blogUpdates = $settings['blog_updates'] ?? false;

        try {
            $dbSessions = DB::table('sessions')
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
                        'device' => $this->parseUserAgent($session->user_agent ?? null),
                    ];
                });
        } catch (\Throwable $e) {
            \Log::warning('Failed fetching sessions in settings: ' . $e->getMessage());
            $dbSessions = collect();
        }

        $currentMatched = $dbSessions->firstWhere('is_current_device', true);

        if ($currentMatched) {
            $this->sessions = collect([$currentMatched])
                ->merge($dbSessions->filter(fn ($s) => ! $s['is_current_device']))
                ->values();
        } else {
            $currentDeviceSession = [
                'id' => session()->getId() ?: 'current',
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Current Device',
                'last_active' => 'Active now',
                'is_current_device' => true,
                'device' => $this->parseUserAgent(request()->userAgent()),
            ];

            $this->sessions = collect([$currentDeviceSession])
                ->merge($dbSessions)
                ->values();
        }
    }

    private function parseUserAgent(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Current Device';
        }

        $platform = 'Unknown OS';
        if (str_contains($userAgent, 'Windows')) {
            $platform = 'Windows';
        } elseif (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS')) {
            $platform = 'macOS';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $platform = 'iOS';
        } elseif (str_contains($userAgent, 'Android')) {
            $platform = 'Android';
        } elseif (str_contains($userAgent, 'Linux')) {
            $platform = 'Linux';
        }

        $browser = 'Browser';
        if (str_contains($userAgent, 'Edg')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        }

        return "{$browser} on {$platform}";
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