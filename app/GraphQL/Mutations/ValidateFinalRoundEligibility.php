<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\CanonicalTournamentTier;
use App\Models\Squad;
use App\Models\TeamRound;
use App\SeasonHistory\RoundLineup;
use App\SeasonHistory\SeasonHistoryLoader;

/**
 * §38 stk. 8 for a saved Scenario's placements and shared persisted Squad context.
 */
class ValidateFinalRoundEligibility
{
    public function __construct(private readonly SeasonHistoryLoader $loader, private readonly TeamRoundLineup $lineup) {}

    /**
     * @param  array{teamRoundId: string, scenarioId?: string|null}  $args
     * @return array{notApplicableReason: ?string, players: list<array<string, mixed>>}
     */
    public function __invoke(mixed $root, array $args): array
    {
        $round = $this->lineup->teamRound($args['teamRoundId']);
        $earlier = null;
        $results = [];
        $coverage = [];
        foreach ($this->lineup->squads($round, $args['scenarioId'] ?? null) as ['index' => $index, 'squad' => $squad, 'players' => $players]) {
            $context = $this->currentContext($squad, $round);
            $coverage[] = $context['coverage'];
            foreach ($players as $player) {
                $status = $context['status'];
                $incompleteReasons = $context['incompleteReasons'];
                $uncountedRounds = [];
                if ($status === null) {
                    $earlier ??= $this->loader->before($round);
                    if ($earlier->matchCountOnSameOrLowerTeam($player->member_ref_id, (int) $squad->team_id) > 0) {
                        $status = 'SATISFIED';
                    } elseif (($uncountedRounds = $earlier->roundsWithoutTeam($player->member_ref_id, (int) $squad->team_id)) !== []) {
                        $status = 'CANNOT_VERIFY';
                        $incompleteReasons = ['INCOMPARABLE_HISTORY'];
                    } else {
                        $status = 'REQUIREMENT_NOT_MET';
                    }
                }
                $results[] = [
                    'squadIndex' => $index,
                    'refId' => $player->member_ref_id,
                    'name' => $player->name,
                    'status' => $status,
                    'incompleteReasons' => $incompleteReasons,
                    'uncountedRounds' => array_map(fn (RoundLineup $round) => [
                        'teamRoundId' => $round->teamRoundId,
                        'name' => $round->name,
                        'round' => $round->round,
                    ], $uncountedRounds),
                ];
            }
        }

        return ['notApplicableReason' => SquadCoverage::notApplicableReason($coverage), 'players' => $results];
    }

    /**
     * A null status means the requirement applies and history must be checked. coverage: why the Squad is left out,
     * a SquadCoverage constant.
     *
     * @return array{status: ?string, incompleteReasons: list<string>, coverage: ?string}
     */
    private function currentContext(Squad $squad, TeamRound $round): array
    {
        $notApplicable = fn (string $coverage) => ['status' => 'NOT_APPLICABLE', 'incompleteReasons' => [], 'coverage' => $coverage];
        if ($squad->is_paper_squad) {
            return $notApplicable(SquadCoverage::PAPER_SQUAD);
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
            return $notApplicable(SquadCoverage::TIER_NOT_COVERED);
        }
        if ($round->round !== null && ! in_array((int) $round->round, $rounds, true)) {
            return $notApplicable(SquadCoverage::ROUND_NOT_COVERED);
        }
        $missing = array_keys(array_filter([
            'MISSING_ROUND' => $round->round === null,
            'UNKNOWN_TIER' => $tier === null,
            'MISSING_TEAM' => $squad->team_id === null,
            'MISSING_SEASON' => $round->season_id === null,
            'MISSING_GAME_DATE' => $round->game_date === null,
        ]));

        return ['status' => $missing === [] ? null : 'CANNOT_VERIFY', 'incompleteReasons' => $missing, 'coverage' => SquadCoverage::COVERED];
    }
}
