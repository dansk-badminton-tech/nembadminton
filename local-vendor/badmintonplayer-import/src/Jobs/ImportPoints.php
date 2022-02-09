<?php

namespace FlyCompany\BadmintonPlayerImport\Jobs;

use App\Models\Member;
use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\PlayerRanking;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
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
        private int $clubId
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
        $rankingList = $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::CURRENT);
        $this->updateMemberPoints($rankingList, $pointsManager);

        return 0;
    }

    /**
     * @param \FlyCompany\BadmintonPlayerAPI\Models\PlayersRanking $rankingList
     * @param PointsManager $pointsManager
     * @return void
     */
    protected function updateMemberPoints(\FlyCompany\BadmintonPlayerAPI\Models\PlayersRanking $rankingList, PointsManager $pointsManager): void
    {
        /** @var PlayerRanking[] $playersByRefId */
        $playersByRefId = [];
        foreach ($rankingList->playerRankings as $playerRanking) {
            $playersByRefId[$playerRanking->playerNumber] = $playerRanking;
        }

        Member::query()->chunk(100, static function (Collection $members) use ($rankingList, $pointsManager, $playersByRefId) {
            foreach ($members as $member) {
                $player = $playersByRefId[$member->refId] ?? null;
                if ($player !== null) {
                    $pointsManager->addPointsByMember($member, $player->singlePoints, 0, $rankingList->getVersionDateCarbon(), $player->getSingleCategory()->value);
                    echo "Found $player->name ($player->playerNumber)" . PHP_EOL;
                    exit();
                }
            }
        });
    }
}