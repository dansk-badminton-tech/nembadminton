<?php


namespace App\Console;

use App\Console\Commands\ImportClubs;
use App\Console\Commands\ImportMembers;
use App\Models\Watch;
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
        $clubWatch = Watch::query()->get();
        $schedule->command(ImportClubs::class, [
            '041020',
            '--club-ids=' . $clubWatch->map(function (Watch $club) {
                return $club->club_id;
            })->join(','),
        ])->appendOutputTo(storage_path('logs/cron.log'))->everyMinute();

        $schedule->command(ImportMembers::class, [
            '041020',
            '--club-ids=' . $clubWatch->map(function (Watch $club) {
                return $club->club_id;
            })->join(','),
        ])->appendOutputTo(storage_path('logs/cron.log'))->everyMinute();
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
