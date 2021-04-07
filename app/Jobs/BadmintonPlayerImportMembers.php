<?php
declare(strict_types = 1);


namespace App\Jobs;

use Carbon\Carbon;
use FlyCompany\Members\MemberManager;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BadmintonPlayerImportMembers implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $clubIds;

    /**
     * Create a new job instance.
     *
     * @param array|String[] $clubIds
     */
    public function __construct(array $clubIds)
    {
        $this->clubIds = $clubIds;
    }

    /**
     * Execute the job.
     *
     * @param BadmintonPlayer $scraper
     * @param MemberManager   $memberManager
     * @param PointsManager   $pointsManager
     *
     * @return void
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function handle(BadmintonPlayer $scraper, MemberManager $memberManager, PointsManager $pointsManager)
    {
        $rankingLists = [
            'DL',
            'HL',
        ];

        $now = Carbon::now();
        $now->subMonth();
        $now->setDay(1);
        $season = $this->calculateSeason($now);
        foreach ($this->clubIds as $clubId) {
            $clubModel = \App\Models\Club::query()->where(['id' => $clubId])->firstOrFail();
            \FlyCompany\Club\Log::createLog((int)$clubId, "Begynder importering af medlemmer fra niveau ranglisten fra sæson $season.", 'member-importer');
            $syncIds = [];
            foreach ($rankingLists as $rankingList) {
                $starting = Carbon::create($season, 7)->setTime(0, 0);
                while ($starting < $now) {
                    $gender = $rankingList === 'HL'
                        ? 'M'
                        : 'K';
                    /** @var \App\Models\Club $clubModel */
                    $playersCollection = $scraper->getRankingListPlayers($rankingList, $season, $clubId, $starting);

                    foreach ($playersCollection as $player) {
                        $rankingListNormalized = \in_array($rankingList, ['HL', 'DL'])
                            ? null
                            : $rankingList;

                        $player->setGender($gender);
                        $member = $memberManager->addOrUpdateMember($player->getRefId(), $player->getName(), $player->getGender());
                        foreach ($player->getPoints() as $point) {
                            $pointsManager->addPointsByMember($member, $point->getPoints(), $point->getPosition(), $starting, $rankingListNormalized, $point->getVintage());
                        }
                        $syncIds[] = $member->id;
                    }
                    $starting->addMonth();
                }
            }
            $membersIds = array_unique($syncIds);
            \FlyCompany\Club\Log::createLog((int)$clubId, "Importering af medlemmer fra sæson $season fuldført", 'member-importer');
//            Log::info("Added ".count($membersIds)." ids: " . implode(',', $membersIds));
            $clubModel->members()->sync($membersIds);
        }
    }

    private function calculateSeason(Carbon $currentTime) : int
    {
        if ($currentTime->month > 6) {
            return $currentTime->year;
        }

        return $currentTime->year - 1;
    }
}
