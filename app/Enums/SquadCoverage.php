<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Why a history Check (§38 stk. 8, §27 stk. 5, §32 stk. 1) leaves out a Squad; a covered Squad has none (null), and its
 * players are checked. A missing fact that decides applicability is never a reason: the Squad is covered and its players
 * can't be verified.
 */
enum SquadCoverage: string
{
    case PAPER_SQUAD = 'PAPER_SQUAD';

    /** The Squad's tier is covered in other rounds, or its tier is unknown. */
    case ROUND_NOT_COVERED = 'ROUND_NOT_COVERED';

    /** The Squad's tier is never covered. */
    case TIER_NOT_COVERED = 'TIER_NOT_COVERED';
}
