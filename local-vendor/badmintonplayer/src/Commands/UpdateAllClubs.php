<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\Commands;

use App\Models\Club;
use App\Models\User;
use FlyCompany\BadmintonPlayer\Jobs\ImportMembers;
use FlyCompany\BadmintonPlayer\Jobs\ImportPoints;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\Club\Log;
use Illuminate\Console\Command;

class UpdateAllClubs extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api-import:update-all-clubs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs for member and points updates for clubs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $clubs = Club::query()->where('initialized', '=', 1)->get();
        foreach ($clubs as $club){
            ImportMembers::withChain([
                new ImportPoints($club->id, RankingPeriodType::CURRENT),
                new ImportPoints($club->id, RankingPeriodType::PREVIOUS)
            ])->dispatch([$club->id]);
        }
        return 0;
    }

}
