<?php

declare(strict_types=1);

namespace App\Enums;

/** The coach's explicit choice for a Squad's ambiguous Liga round-12 match. */
enum MatchKind: string
{
    case MEDAL_FINAL = 'medal_final';
    case QUALIFICATION = 'qualification';
}
