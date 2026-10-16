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

// Fetch new blog articles every 3 days at midnight (EAT)
Schedule::command(FetchBlogContent::class)->cron('0 0 */3 * *')->timezone('Africa/Addis_Ababa');

// Send daily reflection reminders every morning at 09:00 AM (EAT)
Schedule::command(SendDailyReminders::class)->dailyAt('09:00')->timezone('Africa/Addis_Ababa');

// Send streak protection reminders every evening at 8:00 PM (20:00 EAT)
Schedule::command(SendStreakReminders::class)->dailyAt('20:00')->timezone('Africa/Addis_Ababa');
