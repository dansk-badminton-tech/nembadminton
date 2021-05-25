<?php


namespace App\Jobs;

use Carbon\Carbon;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        $rankingLists = BadmintonPlayer::rankingLists();
        $now = Carbon::now();
        $season = 2020;
        foreach ($rankingLists as $rankingList) {
            \FlyCompany\Club\Log::createLog($this->clubId, "Opdater point fra rangliste: $rankingList. Fra sæson: $season til nu", 'points-importer');
            $starting = Carbon::create($season, 7)->setTime(0, 0);
            while ($starting < $now) {
                $season = BadmintonPlayer::calculateSeason($starting);
                $playersCollection = $scraper->getRankingListPlayers($rankingList, $season, $this->clubId, $starting);

                foreach ($playersCollection as $player) {
                    $rankingListNormalized = BadmintonPlayerHelper::rankingListNormalized($rankingList);
                    foreach ($player->points as $point) {
                        try {
                            $pointsManager->addPointsByName($player->name, $point->getPoints(), $point->getPosition(), $starting, $rankingListNormalized, $point->getVintage());
                        } catch (ModelNotFoundException $exception) {
                            Log::info("Skipping: $player->name could not find player");
                        }
                    }
                }
                $starting->addMonth();
            }
            \FlyCompany\Club\Log::createLog($this->clubId, "Opdater point fra rangliste: $rankingList fuldført", 'points-importer');
        }

        return 0;
    }
}
