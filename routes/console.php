<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use App\Console\Commands\SendDailyReminders;
use App\Console\Commands\SendStreakReminders;
use App\Console\Commands\FetchBlogContent;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fetch new blog articles every 3 days at midnight
Schedule::command(FetchBlogContent::class)->cron('0 0 */3 * *');

// Send daily reflection reminders every morning at 09:00 AM
Schedule::command(SendDailyReminders::class)->dailyAt('09:00');

// Send streak protection reminders every evening at 8:00 PM (20:00)
Schedule::command(SendStreakReminders::class)->dailyAt('20:00');
