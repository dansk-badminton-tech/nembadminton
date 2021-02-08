<?php


namespace App\Console;

use App\Console\Commands\ImportClubs;
use App\Console\Commands\ImportDownloadRanking;
use App\Console\Commands\ImportMembers;
use App\Console\Commands\JobImportMembers;
use App\Models\Watch;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(ImportDownloadRanking::class)
                 ->appendOutputTo(storage_path('logs/cron.log'))
                 ->dailyAt('01:10');

        $schedule->command(JobImportMembers::class)
                 ->appendOutputTo(storage_path('logs/cron.log'))
                 ->dailyAt('01:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
