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
        // Check if there are users who have not journaled today.
        $users = User::whereDoesntHave('entries', function ($query) {
            $query->whereDate('created_at', today());
        })->get();

        //                $user = User::find(1);

        foreach ($users as $user) {
            $user->notify(new DailyJournalReminder());
        }

        $this->info('Daily journal reminders sent successfully!');
    }
}
