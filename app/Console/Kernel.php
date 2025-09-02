<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendDailyReminders;
use App\Console\Commands\SendStreakReminders;
use App\Console\Commands\FetchBlogContent;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // We can leave this empty if we are using the commands() method
        // Or keep the command classes here as well for redundancy
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run the daily reminder every day at 08:55 AM
        $schedule->command(SendDailyReminders::class)->dailyAt('08:55');

        // Run the streak reminder every day at 7:00 PM
        $schedule->command(SendStreakReminders::class)->dailyAt('19:00');

        // Fetch blog content twice daily
        $schedule->command(FetchBlogContent::class)->twiceDaily(8, 20);

        // Process queued jobs every minute
        $schedule->command('queue:work --once')->everyMinute();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        // Let's add the command classes directly here as well to be sure
        $this->load(base_path('app/Console/Commands'));


        require base_path('routes/console.php');
    }
}
