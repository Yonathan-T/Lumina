<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\DailyJournalReminder;

class SendDailyReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily journal reminders to users.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNotNull('email')->whereDoesntHave('entries', function ($query) {
            $query->whereDate('created_at', today());
        })->get();

        $notifiedCount = 0;
        foreach ($users as $user) {
            $settings = $user->settings ?? [];
            if (empty($settings['daily_reminder'])) {
                continue;
            }

            $user->notify(new DailyJournalReminder());
            $notifiedCount++;
        }

        $this->info("Daily journal reminders sent successfully! Total sent: {$notifiedCount}");
        return self::SUCCESS;
    }
}
