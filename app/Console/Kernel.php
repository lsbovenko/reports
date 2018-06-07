<?php

namespace App\Console;

use App\Console\Commands\SendReportOvertime;
use App\Console\Commands\SendReportReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SendReportReminder::class,
        SendReportOvertime::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('mail:send-reminder')
            ->weekdays()
            ->at('9:00')
            ->appendOutputTo(storage_path('logs/schedule.log'));

        $schedule
            ->command('mail:send-overtime')
            ->monthlyOn(1, '15:00')
            ->appendOutputTo(storage_path('logs/schedule.log'));
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
