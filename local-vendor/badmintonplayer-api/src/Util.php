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

    /**
     * @param Carbon $birthday
     * @return Vintage
     */
    public static function calculateVintage(Carbon $birthday, ?Carbon $seasonStart = null): Vintage
    {

        if($seasonStart === null){
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

    /**
     * @param PlayerRanking $playerRanking
     * @param Carbon $version
     * @return array
     */
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

    /**
     * @param PlayerRanking $playerRanking
     * @param Carbon $version
     * @param int $points
     * @param string|null $category
     * @return Point
     */
    private static function makePoint(
        PlayerRanking $playerRanking,
        Carbon $version,
        int $points,
        ?string $category
    ): Point {
        $point = new Point();
        $point->vintage = $playerRanking->getVintage()->value;
        $point->points = $points;
        $point->category = $category;
        $point->position = 0;
        $point->version = $version->format('Y-m-d');
        return $point;
    }

    public static function isYoungPlayer(Vintage $vintage) : bool
    {
        return in_array($vintage, [Vintage::U17, Vintage::U19], true);
    }

    public static function calculateVintageByRefId(string $refId, ?Carbon $season = null) : Vintage
    {
        $birthdayStr = Str::substr($refId, 0, 6);
        $birthday = Carbon::createFromFormat('ymd', $birthdayStr);
        return self::calculateVintage($birthday, $season);
    }

}
