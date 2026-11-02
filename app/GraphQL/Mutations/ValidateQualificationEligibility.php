<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\CanonicalTournamentTier;
use App\Enums\SquadCoverage;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\TeamRound;
use Closure;

/** §27 stk. 5's round-number alternatives only; the calendar-year alternative is not evaluated. */
class ValidateQualificationEligibility extends ValidateHistoryCheck
{
    protected function playerResult(SquadMember $player, Squad $squad, TeamRound $round, array $squadCheck, Closure $earlier): array
    {
        $status = $squadCheck['status'];
        $incompleteReasons = $squadCheck['incompleteReasons'];
        $uncountedRounds = [];
        if ($status === null) {
            $count = $earlier()->matchCountOnSameOrLowerTeam($player->member_ref_id, (int) $squad->team_id);
            if ($count >= 3 || ($count >= 2 && $earlier()->matchCountOnSameOrLowerTeamInRounds($player->member_ref_id, (int) $squad->team_id, 7, 11) > 0)) {
                $status = 'SATISFIED';
            } elseif (($uncountedRounds = $earlier()->roundsWithoutTeam($player->member_ref_id, (int) $squad->team_id)) !== []) {
                $status = 'CANNOT_VERIFY';
                $incompleteReasons = ['INCOMPARABLE_HISTORY'];
            } else {
                $status = 'REQUIREMENT_NOT_MET';
            }
        }

        return [
            'status' => $status,
            'incompleteReasons' => $incompleteReasons,
            'uncountedRounds' => self::uncountedRounds($uncountedRounds),
        ];
    }

    protected function squadCheck(Squad $squad, TeamRound $round): array
    {
        if ($squad->is_paper_squad) {
            return self::notCovered(SquadCoverage::PAPER_SQUAD);
        }
        $tier = CanonicalTournamentTier::tryFromRawName($squad->effectiveTier());
        if ($tier !== null && ! in_array($tier, [CanonicalTournamentTier::DIVISION_2, CanonicalTournamentTier::DIVISION_3, CanonicalTournamentTier::DANMARKSSERIEN], true)) {
            return self::notCovered(SquadCoverage::TIER_NOT_COVERED);
        }
        if ($round->round !== null && (int) $round->round !== 12) {
            return self::notCovered(SquadCoverage::ROUND_NOT_COVERED);
        }

        return self::covered([
            'MISSING_ROUND' => $round->round === null,
            'UNKNOWN_TIER' => $tier === null,
            'MISSING_TEAM' => $squad->team_id === null,
            'MISSING_SEASON' => $round->season_id === null,
        ]);
    }
}
