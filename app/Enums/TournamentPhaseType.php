<?php

declare(strict_types=1);

namespace App\Enums;

enum TournamentPhaseType: string
{
    case REGULAR_SEASON = 'REGULAR_SEASON';
    case PROMOTION_RELEGATION = 'PROMOTION_RELEGATION';
    case PLAYOFF = 'PLAYOFF';
    case BYES_PAPER_TEAM = 'BYES_PAPER_TEAM';
    case OTHER = 'OTHER';
}
