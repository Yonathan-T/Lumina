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
        $users = User::whereNotNull('email')->get();
        $notifiedCount = 0;

        foreach ($users as $user) {
            $settings = $user->settings ?? [];
            if (empty($settings['streak_alerts'])) {
                continue;
            }

            $jeopardyStreak = StreakService::getStreakInJeopardy($user->id);

            if ($jeopardyStreak > 0) {
                $user->notify(new StreakReminder($jeopardyStreak));
                $this->info("Streak reminder sent to user {$user->id} ({$user->email}) with streak {$jeopardyStreak}");
                $notifiedCount++;
            }
        }

        $this->info("Streak reminders process completed. Total sent: {$notifiedCount}");
        return self::SUCCESS;
    }
}
