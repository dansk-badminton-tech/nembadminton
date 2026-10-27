<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use FlyCompany\TeamFight\ScenarioManager;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * The players of a TeamRound's lineup for the advisory checks: a saved Scenario's, or the Official Lineup without one.
 */
class TeamRoundLineup
{
    public function __construct(private readonly ScenarioManager $scenarios) {}

    /**
     * The TeamRound with its Squads, Teams and tiers loaded.
     */
    public function teamRound(string $teamRoundId): TeamRound
    {
        return TeamRound::query()->with('squads.team.tier', 'officialScenario')->findOrFail($teamRoundId);
    }

    /**
     * Each Squad with its players, each player once per Squad, in Squad order.
     *
     * @return list<array{index: int, squad: Squad, players: list<SquadMember>}>
     *
     * @throws AuthorizationException When the Scenario isn't one of the TeamRound's.
     */
    public function squads(TeamRound $round, ?string $scenarioId): array
    {
        // Resolve through the authorized TeamRound, never through an unchecked client Scenario id.
        $scenario = null;
        if ($scenarioId !== null) {
            /** @var TeamRoundScenario|null $scenario */
            $scenario = $round->scenarios()->find($scenarioId);
            if ($scenario === null) {
                throw new AuthorizationException;
            }
        }
        $squads = [];
        foreach ($round->squads->values() as $index => $squad) {
            $squad->setRelation('teamRound', $round);
            $players = [];
            foreach ($this->scenarios->getCategories($squad, $scenario?->id) as $category) {
                foreach ($category->players as $player) {
                    $players[$player->member_ref_id] = $player;
                }
            }
            $squads[] = ['index' => $index, 'squad' => $squad, 'players' => array_values($players)];
        }

        return $squads;
    }
}
