<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI;

use Carbon\Carbon;

enum RankingPeriodType: string
{

    case CURRENT = 'Current';
    case PREVIOUS = 'Previous';

    public static function fromDate(Carbon $date) : self{
        if (Carbon::now()->month === $date->month){
            return self::CURRENT;
        }
        return self::PREVIOUS;
    }

}
