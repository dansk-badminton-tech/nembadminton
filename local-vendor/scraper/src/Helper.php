<?php

declare(strict_types=1);

namespace FlyCompany\Scraper;

use FlyCompany\Scraper\Enum\LeagueType;
use Illuminate\Support\Str;

class Helper
{

    /**
     * @param  string  $league
     * @return LeagueType
     */
    public static function convertToLeagueType(string $league) : LeagueType
    {
        if(Str::contains($league, 'liga')){
            return LeagueType::LIGA;
        }
        if(Str::contains($league, '1. division')){
            return LeagueType::FIRSTDIVISION;
        }
        return LeagueType::OTHER;
    }

}
