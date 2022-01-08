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

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
     * @param  BadmintonPlayer  $scraper
     * @param  PointsManager  $pointsManager
     *
     * @return int
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(BadmintonPlayer $scraper, PointsManager $pointsManager) : int
    {
        $rankingLists = BadmintonPlayer::rankingLists();

        $seasons = $this->generateSeasons();
        foreach ($rankingLists as $rankingList) {
            foreach ($seasons as $season){
                \FlyCompany\Club\Log::createLog($this->clubId, "Opdater point fra rangliste: $rankingList. Fra sæson: $season", 'points-importer');
                /** @var Carbon[] $rankingMonths */
                $rankingMonths = BadmintonPlayerHelper::filterToRankingMonths($scraper->getVersions($season));
                foreach ($rankingMonths as $starting){
                    $playersCollection = $scraper->getRankingListPlayersByClub($rankingList, $season, $this->clubId, $starting);
                    foreach ($playersCollection as $player) {
                        $rankingListNormalized = BadmintonPlayerHelper::rankingListNormalized($rankingList);
                        foreach ($player->points as $point) {
                            try {
                                $pointsManager->addPointsByName($player->name, $point->getPoints(), $point->getPosition(), $starting, $rankingListNormalized, $point->getVintage());
                            } catch (ModelNotFoundException) {
                                Log::info("Skipping: $player->name could not find player");
                            }
                        }
                    }
                }
                \FlyCompany\Club\Log::createLog($this->clubId, "Opdater point fra rangliste: $rankingList fuldført. Fra sæson: $season", 'points-importer');
            }
        }

        return 0;
    }

    private function generateSeasons() : array{
        $now = Carbon::now();
        $currentSeason = BadmintonPlayer::calculateSeason($now);
        for ($season = 2020; $season <= $currentSeason; $season++){
            $seasons[] = $season;
        }
        return $seasons;
    }
}
