<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper;

use Carbon\Carbon;
use FlyCompany\Scraper\Models\Category;
use FlyCompany\Scraper\Models\Player;

class Enricher
{

    private BadmintonPlayer $scraper;

    public function __construct(BadmintonPlayer $scraper)
    {
        $this->scraper = $scraper;
    }

    /**
     * @param array|Category[] $categories
     * @param Carbon           $version
     * @param int              $season
     * @param string           $clubId
     *
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function categories(array $categories, Carbon $version, int $season, string $clubId) : void
    {
        foreach ($categories as $category) {
            $rankingList = BadmintonPlayer::getRankingList($category->category,);
            $badmintonPlayers = $this->scraper->getRankingListPlayers($rankingList, $season, $clubId, $version);
            foreach ($badmintonPlayers as $badmintonPlayer) {
                $rankingListNormalized = !\in_array($rankingList, ['HL', 'DL'])
                    ? $rankingList
                    : null;
                foreach ($badmintonPlayer->points as $point) {

                }
            }
        }
    }

}
