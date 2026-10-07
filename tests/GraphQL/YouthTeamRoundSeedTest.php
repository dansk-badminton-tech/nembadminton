<?php

namespace Tests\GraphQL;

use App\Models\SquadMember;
use App\Models\TeamRound;
use Database\Seeders\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

/**
 * The seeded "Ungdom" team rounds (see YouthTeamRoundSeeder) must produce
 * exactly the Youth Player conflicts they were built for, and nothing else.
 */
class YouthTeamRoundSeedTest extends TestCase
{
    use DatabaseMigrations;
    use MakesGraphQLRequests;

    private const YOUTH_PLAYER = 'Aske Groth Jensen';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestingDataSeeder::class);
    }

    public function test_conflict_round_reports_only_the_youth_conflicts_within_squads(): void
    {
        $input = $this->validationInput(TeamRound::where('name', 'Ungdom - 3. runde (konflikter)')->sole());

        $this->assertSame([true, true], $this->spotsFulfilled($input));

        $conflicts = collect($this->mutate('validateSquads', $input, 'isYouthPlayer hasYouthPlayerPartner belowPlayer { name }'))
            ->map(fn (array $conflict) => [
                $conflict['name'],
                $conflict['category'],
                $conflict['isYouthPlayer'],
                $conflict['hasYouthPlayerPartner'],
                collect($conflict['belowPlayer'])->pluck('name')->all(),
            ])
            ->sortBy(fn (array $row) => $row[1].$row[0])
            ->values()
            ->all();

        $this->assertSame([
            // Doubles: the senior partner of a Youth Player sits above a stronger pair
            ['Aske Groth Jensen', 'HD', true, false, ['Lauge Almlund Højgaard', 'Jakob Christensen']],
            ['Jesper Lauge Andersen', 'HD', false, true, ['Lauge Almlund Højgaard', 'Jakob Christensen']],
            // Singles: a senior with more points is placed below a Youth Player
            ['Aske Groth Jensen', 'HS', true, false, ['Jakob Christensen']],
        ], $conflicts);
    }

    public function test_youth_players_are_skipped_in_the_cross_squad_validation(): void
    {
        $input = $this->validationInput(TeamRound::where('name', 'Ungdom - 3. runde (konflikter)')->sole());

        // Mathilde Hay-Schmidt (U19, DS 2113) on Hold 2 has more points than
        // Nanna Reese (DS 1876) on Hold 1, but Youth Players are not compared.
        $this->assertSame([], $this->mutate('validateCrossSquads', $input, 'isYouthPlayer'));
    }

    public function test_earlier_rounds_in_the_season_are_valid_and_place_the_youth_player_differently(): void
    {
        $rounds = TeamRound::where('name', 'like', 'Ungdom - %')->orderBy('round')->get();

        $this->assertSame([1, 2, 3], $rounds->pluck('round')->all());
        $this->assertSame([2025], $rounds->pluck('season_id')->unique()->values()->all());
        $this->assertSame([1], $rounds->pluck('clubhouse_id')->unique()->values()->all());

        foreach ($rounds->take(2) as $round) {
            $input = $this->validationInput($round);
            $this->assertSame([true, true], $this->spotsFulfilled($input), $round->name);
            $this->assertSame([], $this->mutate('validateSquads', $input, 'isYouthPlayer'), $round->name);
            $this->assertSame([], $this->mutate('validateCrossSquads', $input, 'isYouthPlayer'), $round->name);
        }

        $placements = $rounds->map(fn (TeamRound $round) => SquadMember::query()
            ->where('name', self::YOUTH_PLAYER)
            ->whereHas('category.squad', fn ($query) => $query->where('team_round_id', $round->id))
            ->with('category.squad')
            ->get()
            ->map(fn (SquadMember $member) => 'Hold '.$member->category->squad->order.' '.$member->category->name)
            ->sort()
            ->values()
            ->all()
        )->all();

        $this->assertSame([
            ['Hold 2 1. HS', 'Hold 2 2. HD'],
            ['Hold 1 3. HD', 'Hold 1 4. HS'],
            ['Hold 1 2. HD', 'Hold 1 3. HS'],
        ], $placements);
    }

    /**
     * Mirrors wrapInTeamAndSquads() in resources/js/admin-v2/views/team-fight/helper.js.
     *
     * @return array<int, array<string, mixed>>
     */
    private function validationInput(TeamRound $round): array
    {
        $round->load('squads.categories.players.points');

        return $round->squads->sortBy('order')->values()->map(fn ($squad) => [
            'name' => 'Team X',
            'squad' => [
                'id' => $squad->id,
                'playerLimit' => $squad->playerLimit,
                'categories' => $squad->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'category' => $category->category,
                    'name' => $category->name,
                    'players' => $category->players->map(fn ($player) => [
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
                    ])->all(),
                ])->all(),
            ],
        ])->all();
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
        $fields = $mutation === 'validateBasicSquads' ? $fields : 'name category '.$fields;

        return $this->graphQL(
            "mutation (\$input: [ValidateTeam!]!) { {$mutation}(input: \$input) { {$fields} } }",
            ['input' => $input]
        )->assertJsonMissingPath('errors')->json("data.{$mutation}");
    }
}
