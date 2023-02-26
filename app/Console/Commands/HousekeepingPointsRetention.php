<?php

namespace App\Console\Commands;

use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HousekeepingPointsRetention extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'housekeeping:points-retention {before : specified in months}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes points';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $before = $this->argument('before');
        $limit = Carbon::now()->subMonths($before);
        $this->line("Deleting points older then {$limit}...");
        $deleted = Point::query()->where('version', '<', $limit)->delete();
        $this->line("Deleted $deleted points");
        return 0;
    }
}
