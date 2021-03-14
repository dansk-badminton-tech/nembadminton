<?php


namespace App\Console\Commands;


use Carbon\Carbon;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\Models\Point;
use Illuminate\Console\Command;

class ImportRankingList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-import:points {date : ranking to import format \'yyyy-mm-dd\'} {season : Season} {club-id : BadmintonPlayer club id} {rankingList : options Level, HS, DS, HD, DD, MxD, MxH}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import ranking points';

    /**
     * Execute the console command.
     *
     * @param BadmintonPlayer $scraper
     * @param PointsManager $pointsManager
     * @return int
     * @throws \JsonException
     */
    public function handle(BadmintonPlayer $scraper, PointsManager $pointsManager) : int
    {

        /** @var Point[] $rankingCollection */
        $version = Carbon::createFromFormat('Y-m-d', $this->argument('date'))->setTime(0,0,0);

        $rankingCollection = $scraper->getRankingListPlayers($this->argument('rankingList'), $this->argument('season'), $this->argument('club-id'), $version);

        foreach ($rankingCollection as $item){
            $pointsManager->addPointsByName($item->getName(), $item->getPoints(), $item->getPosition(), $version, null, $item->getVintage());
        }

        return 0;
    }

}
