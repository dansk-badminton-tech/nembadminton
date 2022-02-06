<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI;

enum RankingPeriodType: string
{

    case CURRENT = 'Current';
    case PREVIOUS = 'Previous';

}
