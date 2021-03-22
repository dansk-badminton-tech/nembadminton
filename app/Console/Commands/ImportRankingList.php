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
    protected $signature = 'badmintonplayer-import:points {club-id : BadmintonPlayer club id}';

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
     * @param PointsManager   $pointsManager
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(BadmintonPlayer $scraper, PointsManager $pointsManager) : int
    {
        $rankingLists = [
            'Level',
            'HS',
            'DS',
            'HD',
            'DD',
            'MxD',
            'MxH',
        ];

        foreach ($rankingLists as $rankingList) {
            $starting = Carbon::create(2018, 7)->setTime(0, 0);
            $now = Carbon::now();
            while ($starting < $now) {
                $season = $this->calculateSeason($starting);
                $this->info('Running for ' . $starting->format('Y-m-d') . ' season ' . $season . ' ranking list ' . $rankingList);
                $rankingCollection = $scraper->getRankingListPlayers($rankingList, $season, $this->argument('club-id'), $starting);

                foreach ($rankingCollection as $item) {
                    $rankingListNormalized = $rankingList !== 'Level'
                        ? $rankingList
                        : null;
                    $pointsManager->addPointsByName($item->getName(), $item->getPoints(), $item->getPosition(), $starting, $rankingListNormalized, $item->getVintage());
                }
                $starting->addMonth();
            }
        }

        return 0;
    }

    private function calculateSeason(Carbon $currentTime) : int
    {
        if ($currentTime->month > 6) {
            return $currentTime->year;
        }

        return $currentTime->year - 1;
    }

}
