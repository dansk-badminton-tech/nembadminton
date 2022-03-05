<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI;

use Carbon\Carbon;

class Util
{

    /**
     * @param Carbon $birthday
     * @return Vintage
     */
    public static function calculateVintage(Carbon $birthday): Vintage
    {
        $diffYears = $birthday->diffInYears(Carbon::now());
        if ($diffYears <= 13) {
            return Vintage::U13;
        }

        if ($diffYears <= 15) {
            return Vintage::U15;
        }

        if ($diffYears <= 17) {
            return Vintage::U17;
        }

        if ($diffYears <= 19) {
            return Vintage::U19;
        }
        return Vintage::SENIOR;
    }

}
