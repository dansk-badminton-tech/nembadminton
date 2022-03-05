<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Commands;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use Illuminate\Console\Command;

class RankingPoints extends Command
{

    protected $signature = 'badmintonplayer-api:points {ranking-period : Ranking period} {--limit= : limit number of fetched players}';

    protected $description = 'Fetch all ranking points';

    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI) : int{
        $rankingList = $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::from($this->argument('ranking-period')), (int)$this->option('limit') ?? 100000);

        echo json_encode($rankingList, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        return 0;
    }
}
