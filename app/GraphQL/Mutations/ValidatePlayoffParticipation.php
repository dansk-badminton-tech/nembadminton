<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\CanonicalTournamentTier;
use App\Enums\SeasonPhase;
use App\Models\Squad;
use App\Models\TeamRound;
use App\SeasonHistory\PlayoffParticipationRequirement;
use App\SeasonHistory\RoundLineup;
use App\SeasonHistory\SeasonHistoryLoader;

/**
 * The Playoff Participation Requirement (§32 stk. 1 a–c) for a saved Scenario's placements, or the Official Lineup's.
 */
class ValidatePlayoffParticipation
{
    public function __construct(private readonly SeasonHistoryLoader $loader, private readonly TeamRoundLineup $lineup) {}

    /**
     * @param  array{teamRoundId: string, scenarioId?: string|null}  $args
     * @return list<array<string, mixed>>
     */
    public function __invoke(mixed $root, array $args): array
    {
        $round = $this->lineup->teamRound($args['teamRoundId']);
        $earlier = null;
        $results = [];
        foreach ($this->lineup->squads($round, $args['scenarioId'] ?? null) as ['index' => $index, 'squad' => $squad, 'players' => $players]) {
            $context = $this->currentContext($squad, $round);
            foreach ($players as $player) {
                $result = [
                    'status' => $context['status'],
                    'incompleteReasons' => $context['incompleteReasons'],
                    'matchCount' => 0,
                    'playedByDeadline' => null,
                    'uncountedRounds' => [],
                ];
                if ($context['status'] === null) {
                    $earlier ??= $this->loader->before($round);
                    $participation = (new PlayoffParticipationRequirement($earlier, (int) $round->season_id, $context['requiredMatchCount']))
                        ->check($player->member_ref_id);
                    $result = [
                        'status' => $participation->status,
                        'incompleteReasons' => $participation->incompleteReasons,
                        'matchCount' => $participation->matchCount,
                        'playedByDeadline' => $participation->playedByDeadline,
                        'uncountedRounds' => $participation->uncountedRounds,
                    ];
                }
                $results[] = [
                    'squadIndex' => $index,
                    'refId' => $player->member_ref_id,
                    'name' => $player->name,
                    ...$result,
                    'requiredMatchCount' => $context['requiredMatchCount'],
                    'uncountedRounds' => array_map(fn (RoundLineup $round) => [
                        'teamRoundId' => $round->teamRoundId,
                        'name' => $round->name,
                        'round' => $round->round,
                    ], $result['uncountedRounds']),
                ];
            }
        }

        return $results;
    }

    /**
     * A null status means the requirement applies and history must be checked.
     *
     * @return array{status: ?string, incompleteReasons: list<string>, requiredMatchCount: int}
     */
    private function currentContext(Squad $squad, TeamRound $round): array
    {
        $notApplicable = ['status' => 'NOT_APPLICABLE', 'incompleteReasons' => [], 'requiredMatchCount' => 0];
        if ($squad->is_paper_squad) {
            return $notApplicable;
        }
        // The Team's own tier, also in a qualification match against a higher tier (§27 stk. 3).
        $tier = CanonicalTournamentTier::tryFromRawName($squad->effectiveTier());
        $requiredMatchCount = $tier === null ? 0 : PlayoffParticipationRequirement::requiredMatchCount($tier);
        if ($tier !== null && $requiredMatchCount === 0) {
            return $notApplicable;
        }
        // Without a tier, 1. division's playoffs are the earliest the requirement can apply.
        $phaseTier = $tier ?? CanonicalTournamentTier::DIVISION_1;
        if ($round->round !== null && SeasonPhase::fromRound((int) $round->round, $phaseTier->value) === SeasonPhase::GRUNDSPIL) {
            return $notApplicable;
        }
        $missing = array_keys(array_filter([
            'MISSING_ROUND' => $round->round === null,
            'UNKNOWN_TIER' => $tier === null,
            'MISSING_SEASON' => $round->season_id === null,
        ]));

        return ['status' => $missing === [] ? null : 'CANNOT_VERIFY', 'incompleteReasons' => $missing, 'requiredMatchCount' => $requiredMatchCount];
    }
}
