<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\CanonicalTournamentTier;
use App\Models\Squad;
use App\Models\TeamRound;
use App\SeasonHistory\RoundLineup;
use App\SeasonHistory\SeasonHistoryLoader;

/**
 * §38 stk. 8 for the editor's current Scenario placements, using shared persisted Squad context.
 * Only the requested TeamRound's Squads are matched; client-provided metadata never establishes eligibility.
 */
class ValidateFinalRoundEligibility
{
    public function __construct(private readonly SeasonHistoryLoader $loader) {}

    /**
     * @param  array{teamRoundId: string, input: list<array<string, mixed>>}  $args
     * @return list<array<string, mixed>>
     */
    public function __invoke(mixed $root, array $args): array
    {
        $round = TeamRound::query()->with('squads.team.tier')->findOrFail($args['teamRoundId']);
        $squads = $round->squads->keyBy('id');
        $earlier = null;
        $results = [];
        foreach ($args['input'] as $index => $team) {
            $squad = $squads->get($team['squad']['id'] ?? null);
            if ($squad === null) {
                continue;
            }
            $players = [];
            foreach ($team['squad']['categories'] ?? [] as $category) {
                foreach ($category['players'] ?? [] as $player) {
                    $players[$player['refId']] = $player;
                }
            }
            foreach ($players as $player) {
                $status = $this->applicability($squad, $round);
                $incompleteReasons = $status === 'CANNOT_VERIFY' ? $this->missingCurrentContext($squad, $round) : [];
                $uncountedRounds = [];
                if ($status === null) {
                    $earlier ??= $this->loader->before($round);
                    if ($earlier->matchCountOnSameOrLowerTeam($player['refId'], (int) $squad->team_id) > 0) {
                        $status = 'SATISFIED';
                    } elseif (($uncountedRounds = $earlier->roundsWithoutTeam($player['refId'], (int) $squad->team_id)) !== []) {
                        $status = 'CANNOT_VERIFY';
                        $incompleteReasons = ['INCOMPARABLE_HISTORY'];
                    } else {
                        $status = 'REQUIREMENT_NOT_MET';
                    }
                }
                $results[] = [
                    'squadIndex' => $index,
                    'refId' => $player['refId'],
                    'name' => $player['name'],
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

        return $results;
    }

    /** null means the requirement applies and history must be checked. */
    private function applicability(Squad $squad, TeamRound $round): ?string
    {
        if ($squad->is_paper_squad) {
            return 'NOT_APPLICABLE';
        }
        $tier = CanonicalTournamentTier::tryFromRawName($squad->effectiveTier());
        if ($tier === null || $round->round === null) {
            return 'CANNOT_VERIFY';
        }
        $applicable = match ($tier) {
            CanonicalTournamentTier::BADMINTONLIGAEN => (int) $round->round === 12,
            CanonicalTournamentTier::DIVISION_1, CanonicalTournamentTier::DIVISION_2,
            CanonicalTournamentTier::DIVISION_3, CanonicalTournamentTier::DANMARKSSERIEN => in_array((int) $round->round, [11, 12], true),
            default => false,
        };

        if (! $applicable) {
            return 'NOT_APPLICABLE';
        }
        if ($squad->team_id === null || $round->season_id === null || $round->game_date === null) {
            return 'CANNOT_VERIFY';
        }

        return null;
    }

    /** @return list<string> */
    private function missingCurrentContext(Squad $squad, TeamRound $round): array
    {
        return array_keys(array_filter([
            'MISSING_ROUND' => $round->round === null,
            'UNKNOWN_TIER' => CanonicalTournamentTier::tryFromRawName($squad->effectiveTier()) === null,
            'MISSING_TEAM' => $squad->team_id === null,
            'MISSING_SEASON' => $round->season_id === null,
            'MISSING_GAME_DATE' => $round->game_date === null,
        ]));
    }
}
