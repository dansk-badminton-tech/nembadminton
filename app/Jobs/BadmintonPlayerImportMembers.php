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

class BadmintonPlayerImportMembers implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $clubIds;

    /**
     * Create a new job instance.
     *
     * @param string $date
     * @param array  $clubIds
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
        foreach ($this->clubIds as $clubId) {
            $syncIds = [];
            foreach ($rankingLists as $rankingList) {
                $season = $this->calculateSeason($now);
                $gender = $rankingList === 'HL'
                    ? 'M'
                    : 'K';
                /** @var \App\Models\Club $clubModel */
                $clubModel = \App\Models\Club::query()->where(['id' => $clubId])->firstOrFail();
                $playersCollection = $scraper->getRankingListPlayers($rankingList, $season, $clubId, $now);

                foreach ($playersCollection as $player) {
                    $rankingListNormalized = \in_array($rankingList, ['HL', 'DL'])
                        ? null
                        : $rankingList;

                    $player->setGender($gender);
                    $member = $memberManager->addMember($player->getRefId(), $player->getName(), $player->getGender());
                    foreach ($player->getPoints() as $point) {
                        $pointsManager->addPointsByMember($member, $point->getPoints(), $point->getPosition(), $now, $rankingListNormalized, $point->getVintage());
                    }
                    $syncIds[] = $member->id;
                }
            }
            $clubModel->members()->sync($syncIds);
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
