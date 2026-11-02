<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\CanonicalTournamentTier;
use App\Enums\SeasonPhase;
use App\Enums\SquadCoverage;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\TeamRound;
use App\SeasonHistory\PlayoffParticipation;
use App\SeasonHistory\PlayoffParticipationRequirement;
use Closure;

/**
 * The Playoff Participation Requirement (§32 stk. 1 a–c) for a saved Scenario's placements, or the Official Lineup's.
 */
class ValidatePlayoffParticipation extends ValidateHistoryCheck
{
    protected function playerResult(SquadMember $player, Squad $squad, TeamRound $round, array $squadCheck, Closure $earlier): array
    {
        /** @var int $requiredMatchCount */
        $requiredMatchCount = $squadCheck['requiredMatchCount'];
        $participation = $squadCheck['status'] === null
            ? (new PlayoffParticipationRequirement($earlier(), (int) $round->season_id, $requiredMatchCount))->check($player->member_ref_id)
            : new PlayoffParticipation($squadCheck['status'], $squadCheck['incompleteReasons'], 0, null, []);

        return [
            'status' => $participation->status,
            'incompleteReasons' => $participation->incompleteReasons,
            'matchCount' => $participation->matchCount,
            'requiredMatchCount' => $requiredMatchCount,
            'playedByDeadline' => $participation->playedByDeadline,
            'uncountedRounds' => self::uncountedRounds($participation->uncountedRounds),
        ];
    }

    protected function squadCheck(Squad $squad, TeamRound $round): array
    {
        if ($squad->is_paper_squad) {
            return self::notCovered(SquadCoverage::PAPER_SQUAD, ['requiredMatchCount' => 0]);
        }
        // The Team's own tier, also in a qualification match against a higher tier (§27 stk. 3).
        $tier = CanonicalTournamentTier::tryFromRawName($squad->effectiveTier());
        $requiredMatchCount = $tier === null ? 0 : PlayoffParticipationRequirement::requiredMatchCount($tier);
        if ($tier !== null && $requiredMatchCount === 0) {
            return self::notCovered(SquadCoverage::TIER_NOT_COVERED, ['requiredMatchCount' => 0]);
        }
        // Without a tier, 1. division's playoffs are the earliest the requirement can apply.
        $phaseTier = $tier ?? CanonicalTournamentTier::DIVISION_1;
        if ($round->round !== null && SeasonPhase::fromRound((int) $round->round, $phaseTier->value) === SeasonPhase::GRUNDSPIL) {
            return self::notCovered(SquadCoverage::ROUND_NOT_COVERED, ['requiredMatchCount' => 0]);
        }

        return self::covered([
            'MISSING_ROUND' => $round->round === null,
            'UNKNOWN_TIER' => $tier === null,
            'MISSING_SEASON' => $round->season_id === null,
        ], ['requiredMatchCount' => $requiredMatchCount]);
    }
}
