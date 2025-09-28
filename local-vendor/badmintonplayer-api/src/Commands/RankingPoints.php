<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Commands;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use Illuminate\Console\Command;

class RankingPoints extends Command
{

    protected $signature = 'badmintonplayer-api:points {ranking-period : Ranking period} {--limit= : limit number of fetched players} {--name= : search for a name} {--club-id= : club id} {--ref-id= : badminton player id}';

    protected $description = 'Fetch all ranking points';

    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI) : int{
        $rankingList = $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::from(ucfirst(strtolower($this->argument('ranking-period')))));

        $name = $this->option('name');
        if($name !== null){
            $rankingList = $rankingList->getPlayerRankingCollection()->searchByName($name);
        }

        $clubId = $this->option('club-id');
        if($clubId !== null){
            $rankingList = $rankingList->getByClubId((int)$clubId);
        }

        $refId = $this->option('ref-id');
        if($refId !== null){
            $rankingList = $rankingList->getPlayerRankingCollection()->getByPlayerNumbers($refId);
        }

        if($refId === null && $name === null && $clubId === null){
            $this->error('No search options provided. Please provide one of the following: --name, --club-id, --ref-id');
            return 1;
        }

        echo json_encode($rankingList, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        return 0;
    }
}
