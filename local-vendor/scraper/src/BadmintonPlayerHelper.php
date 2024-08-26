<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use FlyCompany\Scraper\Models\Player;
use FlyCompany\Scraper\Models\Point;
use Illuminate\Support\Collection;

class BadmintonPlayerHelper
{

    /**
     * @param  array|Carbon[]  $versions
     * @return Collection
     */
    public static function filterToRankingMonths(array $versions): Collection
    {
        $versions = new Collection($versions);
        return $versions->groupBy('month')->map(static function(Collection $dates){
            return $dates->shift();
        });

    }

    /**
     * @param  array  $versions
     * @return Carbon[]
     */
    public static function convertToCarbonObjects(array $versions): array
    {
        $carbons = array_values(array_filter(array_map(static function ($version) {
            try {
                $carbon = Carbon::createFromFormat('m/d/Y', $version['Value'])->setHour(0)->setMinutes(0)->setSecond(0);
            } catch (InvalidFormatException) {
                $carbon = null;
            }
            return $carbon;
        }, $versions), 'is_object'));
        sort($carbons);
        return array_filter($carbons, static function(Carbon $carbon){return $carbon >= BadmintonPlayerHelper::getCurrentSeasonStart(); });
    }

    /**
     * @param array $rankingLists
     *
     * @return Player[]
     */
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

    public static function getCurrentSeason() : int{
        return self::getCurrentSeasonStart()->year;
    }

    public static function getCurrentSeasonStart() : Carbon
    {
        $now = Carbon::now();
        if ($now->month < 7){
            $now->subYear();
        }
        return self::makeSeasonStart($now->year);
    }

    public static function makeSeasonStart(int $seasonStartYear) : Carbon {
        return Carbon::createFromDate($seasonStartYear, 8,1)->setTime(0, 0, 0);
    }

}
