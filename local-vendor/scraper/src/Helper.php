<?php

declare(strict_types=1);

namespace FlyCompany\Scraper;

use Illuminate\Support\Str;

class Helper
{

    /**
     * @param  string  $league
     * @return string
     */
    public static function convertToLeagueType(string $league) : string
    {
        if(Str::contains($league, 'liga')){
            return 'LIGA';
        }
        if(Str::contains($league, '1. division')){
            return 'FIRSTDIVISION';
        }
        return 'OTHER';
    }

}
