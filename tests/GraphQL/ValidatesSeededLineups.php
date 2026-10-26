<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Models\Member;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\SquadReserve;
use App\Models\TeamRound;
use App\Models\User;

/**
 * Validates the lineups of seeded TeamRounds with the validation mutations, as the editor does.
 */
trait ValidatesSeededLineups
{
    /**
     * Mirrors wrapInTeamAndSquads() in resources/js/admin-v2/views/team-fight/helper.js, for one lineup: a Scenario's,
     * or with null the lineup outside any Scenario.
     *
     * @return array<int, array<string, mixed>>
     */
    private function validationInput(TeamRound $round, ?int $scenarioId): array
    {
        return $round->squads->sortBy('order')->values()->map(fn (Squad $squad) => [
            'name' => 'Team X',
            'squad' => [
                'id' => $squad->id,
                'playerLimit' => $squad->playerLimit,
                'isPaperSquad' => (bool) $squad->is_paper_squad,
                'categories' => $squad->categories()
                    ->where('team_round_scenario_id', $scenarioId)
                    ->with('players.points')
                    ->get()
                    ->map(fn ($category) => [
                        'id' => $category->id,
                        'category' => $category->category,
                        'name' => $category->name,
                        'players' => $category->players->map($this->validationPlayer(...))->all(),
                    ])->all(),
                // The replaced player no longer stands on the Squad; their points come from the ranking.
                'reserves' => $squad->reserves()->inLineup($scenarioId)->get()->map(fn (SquadReserve $reserve) => [
                    'reserveRefId' => $reserve->reserve_ref_id,
                    'replaced' => $this->rankedPlayer($reserve->replaced_ref_id, (string) ($squad->version ?? $round->version)),
                ])->all(),
            ],
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function rankedPlayer(string $refId, string $version): array
    {
        $member = Member::query()->where('refId', $refId)->sole();

        return [
            'gender' => $member->gender === 'M' ? 'MEN' : 'WOMEN',
            'name' => $member->name,
            'refId' => $refId,
            'points' => $member->points()->whereDate('version', $version)->get()->map(fn ($point) => [
                'category' => $point->category,
                'points' => $point->points,
                'position' => $point->position,
                'vintage' => $point->vintage,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validationPlayer(SquadMember $player): array
    {
        return [
            'id' => $player->id,
            'gender' => $player->gender === 'M' ? 'MEN' : 'WOMEN',
            'name' => $player->name,
            'refId' => $player->member_ref_id,
            'points' => $player->points->map(fn ($point) => [
                'category' => $point->category,
                'points' => $point->points,
                'position' => $point->position,
                'vintage' => $point->vintage,
            ])->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $input
     * @return array<int, bool>
     */
    private function spotsFulfilled(array $input): array
    {
        return collect($this->mutate('validateBasicSquads', $input, 'index spotsFulfilled'))
            ->pluck('spotsFulfilled')
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $input
     * @return array<int, array<string, mixed>>
     */
    private function mutate(string $mutation, array $input, string $fields): array
    {
        return $this->graphQL(
            "mutation (\$input: [ValidateTeam!]!) { {$mutation}(input: \$input) { {$fields} } }",
            ['input' => $input]
        )->assertJsonMissingPath('errors')->json("data.{$mutation}");
    }

    /**
     * The editor's cross-Squad check (§38 stk. 4), which reads the lineup on the server, as User 1 who edits the seeded
     * TeamRounds: a Scenario's lineup, or with null the official one.
     *
     * @return array<int, array<string, mixed>>
     */
    private function teamRoundCrossSquads(TeamRound $round, ?int $scenarioId, string $fields): array
    {
        $this->actingAs(User::query()->findOrFail(1), 'api');

        return $this->graphQL(
            "mutation (\$id: ID!, \$scenarioId: ID) { validateTeamRoundCrossSquads(teamRoundId: \$id, scenarioId: \$scenarioId) { {$fields} } }",
            ['id' => $round->id, 'scenarioId' => $scenarioId]
        )->assertJsonMissingPath('errors')->json('data.validateTeamRoundCrossSquads');
    }
}
