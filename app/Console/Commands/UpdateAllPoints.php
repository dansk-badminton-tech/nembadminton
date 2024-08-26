<?php
declare(strict_types = 1);

namespace App\Console\Commands;

use App\Jobs\BadmintonPlayerImportMembers;
use App\Jobs\BadmintonPlayerImportPoints;
use App\Models\Club;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateAllPoints extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-scrape-import:update-all-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches update jobs for all clubs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var Club[] $clubs */
        $clubs = Club::query()->where('initialized', '=', 1)->get();
        foreach ($clubs as $club){
            BadmintonPlayerImportPoints::dispatch($club->badmintonPlayerId);
        }
        return 0;
    }
}
