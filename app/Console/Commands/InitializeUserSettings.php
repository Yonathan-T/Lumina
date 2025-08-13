<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class InitializeUserSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:initialize-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize settings for all existing users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        $defaultSettings = [
            'daily_reminder' => false,
            'streak_alerts' => false,
            'blog_updates' => false,
            'dark_mode' => false
        ];

        $updated = 0;

        foreach ($users as $user) {
            $currentSettings = $user->settings ?? [];

            // Normalize existing settings to boolean values
            $normalizedSettings = [
                'daily_reminder' => (bool) ($currentSettings['daily_reminder'] ?? false),
                'streak_alerts' => (bool) ($currentSettings['streak_alerts'] ?? false),
                'blog_updates' => (bool) ($currentSettings['blog_updates'] ?? false),
                'dark_mode' => (bool) ($currentSettings['dark_mode'] ?? false),
            ];

            $user->update(['settings' => $normalizedSettings]);
            $updated++;

            $this->info("Updated settings for user: {$user->name}");
        }

        $this->info("Settings initialization complete! Updated {$updated} users.");
    }
}
