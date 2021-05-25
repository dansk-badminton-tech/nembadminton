<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper;

use FlyCompany\Scraper\Models\Player;
use FlyCompany\Scraper\Models\Point;

class BadmintonPlayerHelper
{

    public static function collapseRankingLists(array $rankingLists) : array
    {
        /** @var Player[] $selectedPlayers */
        $selectedPlayers = [];

        /** @var Player[] $players */
        foreach ($rankingLists as $rankingList => $players) {
            foreach ($players as $player) {
                static::applyCategory($player->points, static::rankingListNormalized($rankingList));
                if (isset($selectedPlayers[$player->name])) {
                    $foundPlayer = $selectedPlayers[$player->name];
                    $foundPlayer->points = \array_merge($foundPlayer->points, $player->points);
                } else {
                    $selectedPlayers[$player->name] = $player;
                }
            }
        }

        return $selectedPlayers;
    }

    /**
     * @param array|Point[] $points
     * @param string|null   $category
     */
    private static function applyCategory(array $points, ?string $category) : void
    {
        foreach ($points as $point) {
            $point->setCategory($category);
        }
    }

    public static function rankingListNormalized(string $rankingList) : ?string
    {
        return !\in_array($rankingList, ['HL', 'DL'])
            ? $rankingList
            : null;
    }

}
