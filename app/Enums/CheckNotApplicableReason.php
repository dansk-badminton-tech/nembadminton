<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Why a history Check (§38 stk. 8, §27 stk. 5, §32 stk. 1) is Not Applicable to a Lineup: no Squad is covered. The
 * GraphQL enum CheckNotApplicableReason.
 */
enum CheckNotApplicableReason: string
{
    case PAPER_SQUADS_ONLY = 'PAPER_SQUADS_ONLY';
    case ROUND_NOT_COVERED = 'ROUND_NOT_COVERED';
    case TIER_NOT_COVERED = 'TIER_NOT_COVERED';

    /**
     * From each Squad's coverage (null: covered): null when a Squad is covered or there is no Squad. PAPER_SQUADS_ONLY
     * when every Squad is a Paper Squad. Otherwise ROUND_NOT_COVERED when another round would cover a Squad, else
     * TIER_NOT_COVERED.
     *
     * @param  list<SquadCoverage|null>  $squads
     */
    public static function fromSquads(array $squads): ?self
    {
        if ($squads === [] || in_array(null, $squads, true)) {
            return null;
        }
        $notPaper = array_filter($squads, fn (SquadCoverage $coverage) => $coverage !== SquadCoverage::PAPER_SQUAD);
        if ($notPaper === []) {
            return self::PAPER_SQUADS_ONLY;
        }

        return in_array(SquadCoverage::ROUND_NOT_COVERED, $notPaper, true) ? self::ROUND_NOT_COVERED : self::TIER_NOT_COVERED;
    }
}
