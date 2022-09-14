<?php

namespace FlyCompany\BadmintonPlayer\Jobs;

use App\Models\Member;
use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\PlayerRanking;
use FlyCompany\BadmintonPlayerAPI\Models\PlayersRanking;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\Scraper\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ImportPoints implements ShouldQueue
{

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param int $clubId
     */
    public function __construct(
        private int $clubId,
        private RankingPeriodType $rankingPeriodType
    )
    {
    }

    /**
     * Execute the job.
     *
     * @return int
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI, PointsManager $pointsManager): int
    {
        $rankingList = $badmintonPlayerAPI->getPlayerRanking($this->rankingPeriodType);
        $this->updateMemberPoints($rankingList, $pointsManager);

        return 0;
    }

    /**
     * @param PlayersRanking $rankingList
     * @param PointsManager $pointsManager
     * @return void
     */
    protected function updateMemberPoints(PlayersRanking $rankingList, PointsManager $pointsManager): void
    {
        /** @var PlayerRanking[] $playersByRefId */
        $playersByRefId = [];
        foreach ($rankingList->playerRankings as $playerRanking) {
            $playersByRefId[$playerRanking->playerNumber] = $playerRanking;
        }

        \FlyCompany\Club\Log::createLog($this->clubId, "Importer point fra ranglisten: {$rankingList->getVersionDateCarbon()->format('Y-m-d')}", 'points-importer');
        Member::query()->club($this->clubId)->chunk(100, function (Collection $members) use ($rankingList, $pointsManager, $playersByRefId) {
            foreach ($members as $member) {
                $player = $playersByRefId[$member->refId] ?? null;
                if ($player !== null && $player->clubID === $this->clubId) {
                    Log::info("Updating $player->name($player->gender) single points to {$player->singlePoints} on ranking list {$rankingList->getVersionDateCarbon()}");
                    $pointsManager->addPointsByMember($member, $player->singlePoints, 0, $rankingList->getVersionDateCarbon(), $player->getSingleCategory()->value, $player->getVintage()->value);

                    Log::info("Updating $player->name($player->gender) double points to {$player->doublePoints} on ranking list {$rankingList->getVersionDateCarbon()}");
                    $pointsManager->addPointsByMember($member, $player->doublePoints, 0, $rankingList->getVersionDateCarbon(), $player->getDoubleCategory()->value, $player->getVintage()->value);

                    Log::info("Updating $player->name($player->gender) mix points to {$player->mixPoints} on ranking list {$rankingList->getVersionDateCarbon()}");
                    $pointsManager->addPointsByMember($member, $player->mixPoints, 0, $rankingList->getVersionDateCarbon(), $player->getMixCategory()->value, $player->getVintage()->value);

                    Log::info("Updating $player->name($player->gender) level points to {$player->niveauPoints} on ranking list {$rankingList->getVersionDateCarbon()}");
                    $pointsManager->addPointsByMember($member, $player->niveauPoints, 0, $rankingList->getVersionDateCarbon(), null, $player->getVintage()->value);
                }
            }
        });
        \FlyCompany\Club\Log::createLog($this->clubId, "Færdig med at importer point fra ranglisten:  {$rankingList->getVersionDateCarbon()->format('Y-m-d')}", 'points-importer');
    }
}
