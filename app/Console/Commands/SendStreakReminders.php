<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\StreakReminder;
use App\Services\StreakService;

class SendStreakReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:streak';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send streak reminders to users whose streak is in danger.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find users whose last journal entry was yesterday and have an active streak.
        $users = User::whereHas('entries', function ($query) {
            $query->whereDate('created_at', today()->subDay());
        })->get();

        // Notify each user who has a streak
        foreach ($users as $user) {
            $currentStreak = StreakService::getCurrentStreak($user->id);
            
            if ($currentStreak > 0) {
                $user->notify(new StreakReminder($currentStreak));
                $this->info("Streak reminder sent to user {$user->id} with streak {$currentStreak}");
            }
        }

        $this->info('Streak reminders sent successfully!');
    }
}
