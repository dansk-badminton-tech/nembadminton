<?php


namespace App\Jobs;

use Carbon\Carbon;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BadmintonPlayerImportPoints implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $clubId;

    /**
     * Create a new job instance.
     *
     * @param int $clubId
     */
    public function __construct(int $clubId)
    {
        $this->clubId = $clubId;
    }

    /**
     * Execute the job.
     *
     * @param BadmintonPlayer $scraper
     * @param PointsManager   $pointsManager
     *
     * @return int
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function handle(BadmintonPlayer $scraper, PointsManager $pointsManager) : int
    {
        $rankingLists = [
            'DL',
            'HL',
            'HS',
            'DS',
            'HD',
            'DD',
            'MxD',
            'MxH',
        ];

        $now = Carbon::now();
        $season = 2020;
        foreach ($rankingLists as $rankingList) {
            \FlyCompany\Club\Log::createLog($this->clubId, "Opdater point fra rangliste: $rankingList. Fra sæson: $season til nu", 'points-importer');
            $starting = Carbon::create($season, 7)->setTime(0, 0);
            while ($starting < $now) {
                $season = $this->calculateSeason($starting);
                $playersCollection = $scraper->getRankingListPlayers($rankingList, $season, $this->clubId, $starting);

                foreach ($playersCollection as $player) {
                    $rankingListNormalized = !\in_array($rankingList, ['HL', 'DL'])
                        ? $rankingList
                        : null;
                    foreach ($player->getPoints() as $point) {
                        $pointsManager->addPointsByName($player->getName(), $point->getPoints(), $point->getPosition(), $starting, $rankingListNormalized, $point->getVintage());
                    }
                }
                $starting->addMonth();
            }
            \FlyCompany\Club\Log::createLog($this->clubId, "Opdatering af point fra rangliste: $rankingList fuldført", 'points-importer');
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
