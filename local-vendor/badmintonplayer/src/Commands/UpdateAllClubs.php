<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\Commands;

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
        $usersWithClubId = User::query()->groupBy('organization_id')->get(['organization_id']);
        foreach ($usersWithClubId as $item){
            ImportMembers::withChain([
                new ImportPoints($item->organization_id, RankingPeriodType::CURRENT),
                new ImportPoints($item->organization_id, RankingPeriodType::PREVIOUS)
            ])->dispatch([$item->organization_id]);
        }
        return 0;
    }

}
