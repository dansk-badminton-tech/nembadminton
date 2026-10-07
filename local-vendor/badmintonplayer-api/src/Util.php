<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Models\PlayerRanking;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\TeamFight\Models\Point;
use Illuminate\Support\Str;

class Util
{
    public static function calculateVintage(Carbon $birthday, ?Carbon $seasonStart = null): Vintage
    {

        if ($seasonStart === null) {
            $seasonStart = BadmintonPlayerHelper::getCurrentSeasonStart();
        }
        $seasonStart->setMonth(1);
        $seasonStart->addYear();

        $diffYears = $birthday->diffInYears($seasonStart);
        if ($diffYears < 9) {
            return Vintage::U9;
        }
        if ($diffYears < 11) {
            return Vintage::U11;
        }
        if ($diffYears < 13) {
            return Vintage::U13;
        }
        if ($diffYears < 15) {
            return Vintage::U15;
        }
        if ($diffYears < 17) {
            return Vintage::U17;
        }
        if ($diffYears < 19) {
            return Vintage::U19;
        }

        return Vintage::SENIOR;
    }

    public static function convertToPointsList(PlayerRanking $playerRanking, Carbon $version): array
    {
        $points = [];
        $points[] = self::makePoint($playerRanking, $version, $playerRanking->niveauPoints, null);
        $points[] = self::makePoint(
            $playerRanking,
            $version,
            $playerRanking->mixPoints,
            $playerRanking->getMixCategory()->value
        );
        $points[] = self::makePoint(
            $playerRanking,
            $version,
            $playerRanking->doublePoints,
            $playerRanking->getDoubleCategory()->value
        );
        $points[] = self::makePoint(
            $playerRanking,
            $version,
            $playerRanking->singlePoints,
            $playerRanking->getSingleCategory()->value
        );

        return $points;
    }

    private static function makePoint(
        PlayerRanking $playerRanking,
        Carbon $version,
        int $points,
        ?string $category
    ): Point {
        $point = new Point;
        $point->vintage = $playerRanking->getVintage()->value;
        $point->points = $points;
        $point->category = $category;
        $point->position = 0;
        $point->version = $version->format('Y-m-d');

        return $point;
    }

    public static function isYoungPlayer(Vintage $vintage): bool
    {
        return in_array($vintage, [Vintage::U17, Vintage::U19], true);
    }

    public static function calculateVintageByRefId(string $refId, ?Carbon $season = null): Vintage
    {
        return self::calculateVintage(self::birthdayFromRefId($refId), $season);
    }

    /**
     * §31 stk. 1: A player must turn 15 no later than 31.12. in the calendar year the season starts.
     */
    public static function isTooYoungForSenior(Carbon $birthday, int $seasonStartYear): bool
    {
        return $birthday->year > $seasonStartYear - 15;
    }

    public static function isTooYoungForSeniorByRefId(string $refId, int $seasonStartYear): bool
    {
        return self::isTooYoungForSenior(self::birthdayFromRefId($refId), $seasonStartYear);
    }

    private static function birthdayFromRefId(string $refId): Carbon
    {
        return Carbon::createFromFormat('ymd', Str::substr($refId, 0, 6));
    }
}
