<?php

declare(strict_types=1);

namespace FlyCompany\Scraper\Enums;

enum RankingList : string
{

    case WOMEN_LEVEL = 'DL';
    case MEN_LEVEL = 'HL';
    case WOMEN_SINGLE = 'DS';
    case MEN_SINGLE = 'HS';
    case WOMEN_DOUBLE = 'DD';
    case MEN_DOUBLE = 'HD';
    case WOMEN_MIX = 'MxD';
    case MEN_MIX = 'MxH';

}
