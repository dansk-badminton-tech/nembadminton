<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\CanonicalTournamentTier;
use App\Enums\SquadCoverage;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\TeamRound;
use Closure;

/**
 * §38 stk. 8 for a saved Scenario's placements and shared persisted Squad context.
 */
class ValidateFinalRoundEligibility extends ValidateHistoryCheck
{
    protected function playerResult(SquadMember $player, Squad $squad, TeamRound $round, array $squadCheck, Closure $earlier): array
    {
        $status = $squadCheck['status'];
        $incompleteReasons = $squadCheck['incompleteReasons'];
        $uncountedRounds = [];
        if ($status === null) {
            if ($earlier()->matchCountOnSameOrLowerTeam($player->member_ref_id, (int) $squad->team_id) > 0) {
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
        // Rounds 11–12 in 1.–3. division and Danmarksserien, round 12 in Badmintonligaen; an unknown tier may be either.
        $rounds = match ($tier) {
            CanonicalTournamentTier::BADMINTONLIGAEN => [12],
            CanonicalTournamentTier::DIVISION_1, CanonicalTournamentTier::DIVISION_2,
            CanonicalTournamentTier::DIVISION_3, CanonicalTournamentTier::DANMARKSSERIEN, null => [11, 12],
            default => [],
        };
        if ($rounds === []) {
            return self::notCovered(SquadCoverage::TIER_NOT_COVERED);
        }
        if ($round->round !== null && ! in_array((int) $round->round, $rounds, true)) {
            return self::notCovered(SquadCoverage::ROUND_NOT_COVERED);
        }

        return self::covered([
            'MISSING_ROUND' => $round->round === null,
            'UNKNOWN_TIER' => $tier === null,
            'MISSING_TEAM' => $squad->team_id === null,
            'MISSING_SEASON' => $round->season_id === null,
            'MISSING_GAME_DATE' => $round->game_date === null,
        ]);
    }
}
