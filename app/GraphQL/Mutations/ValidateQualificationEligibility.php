<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\CanonicalTournamentTier;
use App\Models\Squad;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use App\SeasonHistory\RoundLineup;
use App\SeasonHistory\SeasonHistoryLoader;
use FlyCompany\TeamFight\ScenarioManager;
use Illuminate\Auth\Access\AuthorizationException;

/** §27 stk. 5's round-number alternatives only; the calendar-year alternative is not evaluated. */
class ValidateQualificationEligibility
{
    public function __construct(private readonly SeasonHistoryLoader $loader, private readonly ScenarioManager $scenarios) {}

    /**
     * @param  array{teamRoundId: string, scenarioId?: string|null}  $args
     * @return list<array<string, mixed>>
     */
    public function __invoke(mixed $root, array $args): array
    {
        $round = TeamRound::query()->with('squads.team.tier', 'officialScenario')->findOrFail($args['teamRoundId']);
        $scenarioId = null;
        if (isset($args['scenarioId'])) {
            /** @var TeamRoundScenario|null $scenario */
            $scenario = $round->scenarios()->find($args['scenarioId']);
            if ($scenario === null) {
                throw new AuthorizationException;
            }
            $scenarioId = $scenario->id;
        }
        $earlier = null;
        $results = [];
        foreach ($round->squads->values() as $index => $squad) {
            $squad->setRelation('teamRound', $round);
            $players = [];
            foreach ($this->scenarios->getCategories($squad, $scenarioId) as $category) {
                foreach ($category->players as $player) {
                    $players[$player->member_ref_id] = $player;
                }
            }
            $context = $this->currentContext($squad, $round);
            foreach ($players as $player) {
                $status = $context['status'];
                $incompleteReasons = $context['incompleteReasons'];
                $uncountedRounds = [];
                if ($status === null) {
                    $earlier ??= $this->loader->before($round);
                    $count = $earlier->matchCountOnSameOrLowerTeam($player->member_ref_id, (int) $squad->team_id);
                    if ($count >= 3 || ($count >= 2 && $earlier->matchCountOnSameOrLowerTeamInRounds($player->member_ref_id, (int) $squad->team_id, 7, 11) > 0)) {
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

        return $results;
    }

    /** @return array{status: ?string, incompleteReasons: list<string>} */
    private function currentContext(Squad $squad, TeamRound $round): array
    {
        if ($squad->is_paper_squad) {
            return ['status' => 'NOT_APPLICABLE', 'incompleteReasons' => []];
        }
        $tier = CanonicalTournamentTier::tryFromRawName($squad->effectiveTier());
        $missing = array_filter([
            'MISSING_ROUND' => $round->round === null,
            'UNKNOWN_TIER' => $tier === null,
            'MISSING_TEAM' => $squad->team_id === null,
            'MISSING_SEASON' => $round->season_id === null,
        ]);
        if (($round->round !== null && (int) $round->round !== 12)
            || ($tier !== null && ! in_array($tier, [CanonicalTournamentTier::DIVISION_2, CanonicalTournamentTier::DIVISION_3, CanonicalTournamentTier::DANMARKSSERIEN], true))) {
            return ['status' => 'NOT_APPLICABLE', 'incompleteReasons' => []];
        }

        return ['status' => $missing === [] ? null : 'CANNOT_VERIFY', 'incompleteReasons' => array_keys($missing)];
    }
}
