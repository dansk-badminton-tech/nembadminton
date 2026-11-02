<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

/**
 * Why a history Check (§38 stk. 8, §27 stk. 5, §32 stk. 1) leaves out a Squad, and the Check-level reason it is Not
 * Applicable to a Lineup: no Squad is covered. A missing fact that decides applicability is never a reason; the Squad is
 * covered and its players can't be verified.
 */
final class SquadCoverage
{
    /** The Squad is covered: its players are checked. */
    public const COVERED = null;

    public const PAPER_SQUAD = 'PAPER_SQUAD';

    /** The Squad's tier is covered in other rounds, or its tier is unknown. */
    public const ROUND_NOT_COVERED = 'ROUND_NOT_COVERED';

    /** The Squad's tier is never covered. */
    public const TIER_NOT_COVERED = 'TIER_NOT_COVERED';

    /**
     * The Check's notApplicableReason from each Squad's coverage: null when a Squad is covered or there is no Squad.
     * PAPER_SQUADS_ONLY when every Squad is a Paper Squad. Otherwise ROUND_NOT_COVERED when another round would cover a
     * Squad, else TIER_NOT_COVERED.
     *
     * @param  list<string|null>  $squads
     */
    public static function notApplicableReason(array $squads): ?string
    {
        if ($squads === [] || in_array(self::COVERED, $squads, true)) {
            return null;
        }
        $notPaper = array_diff($squads, [self::PAPER_SQUAD]);
        if ($notPaper === []) {
            return 'PAPER_SQUADS_ONLY';
        }

        return in_array(self::ROUND_NOT_COVERED, $notPaper, true) ? self::ROUND_NOT_COVERED : self::TIER_NOT_COVERED;
    }
}
