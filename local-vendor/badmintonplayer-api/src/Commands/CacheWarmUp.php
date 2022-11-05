<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Commands;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use Illuminate\Console\Command;

class CacheWarmUp extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api:cache-warm-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches data from badmintonplayer API and save it to cache';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI) : int
    {
        $this->getOutput()->writeln('Warming up cache');
        $badmintonPlayerAPI->overrideCache();
        $this->getOutput()->writeln('Fetching current month ranking');
        $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::CURRENT);
        $this->getOutput()->writeln('Fetching previous month ranking');
        $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::PREVIOUS);
        $this->getOutput()->writeln('Fetching coming league matches');
        $badmintonPlayerAPI->getCurrentLeagueMatches();
        $this->getOutput()->writeln('Fetching played league matches');
        $badmintonPlayerAPI->getPlayedLeagueMatches();


        return 0;
    }

}
