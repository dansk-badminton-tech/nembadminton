<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\Jobs;

use App\Models\Club;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\PlayerRanking;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\Members\MemberManager;
use FlyCompany\Members\PointsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;

class ImportMembers implements ShouldQueue
{

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $clubIds;

    public function __construct(array $clubIds)
    {
        $this->clubIds = $clubIds;
    }

    /**
     * @param BadmintonPlayerAPI $badmintonPlayerAPI
     * @param MemberManager $memberManager
     * @param PointsManager $pointsManager
     * @return int
     * @throws InvalidArgumentException
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI, MemberManager $memberManager, PointsManager $pointsManager): int
    {
        $rankingList = $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::CURRENT);

        foreach ($this->clubIds as $clubId){
            \FlyCompany\Club\Log::createLog($clubId, "Importerer medlemmer fra niveau ranglisten {$rankingList->getVersionDateCarbon()->format('Y-m-d')}", 'member-importer');
            /** @var Club $clubModel */
            $clubModel = Club::query()->where(['id' => $clubId])->firstOrFail();

            $membersIds = [];
            /** @var PlayerRanking[] $players */
            $players = $rankingList->getPlayerRankingCollection()->getByClubId($clubId);
            foreach ($players as $player){
                if($player->niveauPoints !== 0 && $player->niveauPoints !== null){
                    Log::info("Upsert $player->name($player->playerNumber) with level points $player->niveauPoints ranking {$rankingList->getVersionDateCarbon()}");
                    $member = $memberManager->addOrUpdateMember($player->playerNumber, $player->name, $player->gender);
                    $pointsManager->addPointsByMember($member, $player->niveauPoints, 0, $rankingList->getVersionDateCarbon(), null, $player->getVintage()->value);
                    $membersIds[] = $member->id;
                }
            }
            $membersIds = array_unique($membersIds);
            $clubModel->members()->sync($membersIds);
            \FlyCompany\Club\Log::createLog($clubId, "Medlemsimport færdig", 'member-importer');
            Log::info("Added ".count($membersIds)." to $clubModel->name1");
        }
        return 0;
    }

}
