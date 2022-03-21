<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Commands;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use Illuminate\Console\Command;

class RankingPoints extends Command
{

    protected $signature = 'badmintonplayer-api:points {ranking-period : Ranking period} {--limit= : limit number of fetched players} {--name= : search for a name} {--club-id= : club id}';

    protected $description = 'Fetch all ranking points';

    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI) : int{
        $rankingList = $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::from($this->argument('ranking-period')), (int)($this->option('limit') ?? 100000));

        $name = $this->option('name');
        if($name !== null){
            $rankingList = $rankingList->getPlayerRankingCollection()->searchByName($name);
        }

        $clubId = $this->option('club-id');
        if($clubId !== null){
            $rankingList = $rankingList->getByClubId((int)$clubId);
        }


        echo json_encode($rankingList, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        return 0;
    }
}
