<?php


namespace App\Console\Commands;

use App\Jobs\BadmintonPlayerImportPoints;
use Carbon\Carbon;
use FlyCompany\Import\Ranking;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\Enums\RankingList;
use Illuminate\Console\Command;

class BadmintonPlayerPointsImporter extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-import:points {club-id : BadmintonPlayer club id} {--rankingList= : Limit import to one RankingList}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importing points for a club from badmintonplayer.dk rankinglist';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \JsonException
     */
    public function handle() : int
    {
        $rankingList = $this->option('rankingList');
        if($rankingList !== null){
            $rankingList = RankingList::from($rankingList);
        }
        BadmintonPlayerImportPoints::dispatchNow($this->argument('club-id'), $rankingList);

        return 0;
    }

}
