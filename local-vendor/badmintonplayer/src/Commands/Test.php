<?php

namespace FlyCompany\BadmintonPlayer\Commands;

use App\Jobs\BadmintonPlayerImportPoints;
use FlyCompany\BadmintonPlayer\Jobs\ImportPoints;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\PlayerRanking;
use FlyCompany\BadmintonPlayerAPI\Models\PlayersRanking;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatch;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Console\Command;

class Test extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api-import:test {player-number : xxxxxx-xx player number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI) : int
    {
        $rankingList = $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::CURRENT);
        $this->line($rankingList->getVersionDateCarbon());
        /** @var PlayerRanking[] $playersByRefId */
        $playersByRefId = [];
        foreach ($rankingList->playerRankings as $index => $playerRanking) {
            if($index % 5000 === 0){
                $this->line('Processed '.$index);
            }
            if($playerRanking->playerNumber == $this->argument('player-number')){
                dump($playerRanking);
            }
        }

        return 0;
    }

}
