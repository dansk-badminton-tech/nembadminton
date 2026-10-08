<?php

namespace Tests\GraphQL;

use App\Models\SquadMember;
use App\Models\TeamRound;
use Database\Seeders\TestingDataSeeder;
use Database\Seeders\YouthTeamRoundSeeder;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

/**
 * The seeded "Ungdom" team rounds (see YouthTeamRoundSeeder) must produce
 * exactly the Youth Player conflicts they were built for, and nothing else.
 */
class YouthTeamRoundSeedTest extends TestCase
{
    use MakesGraphQLRequests;

    protected function setUp(): void
    {
        parent::setUp();

        // TestingDataSeeder needs fresh auto-increment ids (Clubhouse 1, User 1), which
        // RefreshDatabase and DatabaseTruncation don't give after other tests have run.
        $this->artisan('migrate:fresh', ['--seed' => true, '--seeder' => TestingDataSeeder::class]);
        $this->beforeApplicationDestroyed(fn () => $this->artisan('migrate:fresh'));
    }

    public function test_conflict_round_reports_only_the_youth_conflicts_within_squads(): void
    {
        $input = $this->validationInput(TeamRound::where('name', YouthTeamRoundSeeder::CONFLICT_ROUND)->sole());

        $this->assertSame([true, true], $this->spotsFulfilled($input));

        $conflicts = collect($this->mutate('validateSquads', $input, 'name category isYouthPlayer hasYouthPlayerPartner belowPlayer { name }'))
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
            // Doubles: pairs with a Youth Player sit above a stronger senior pair
            ['Aske Groth Jensen', 'HD', true, false, ['Victor R. Andersen', 'Jakob Christensen']],
            ['Jesper Lauge Andersen', 'HD', false, true, ['Victor R. Andersen', 'Jakob Christensen']],
            ['Lars Juncker', 'HD', false, true, ['Victor R. Andersen', 'Jakob Christensen']],
            ['Lauge Almlund Højgaard', 'HD', true, false, ['Victor R. Andersen', 'Jakob Christensen']],
            // Singles: a senior with more points is placed below a Youth Player
            ['Aske Groth Jensen', 'HS', true, false, ['Jakob Christensen']],
        ], $conflicts);
    }

    public function test_youth_players_are_skipped_in_the_cross_squad_validation(): void
    {
        $input = $this->validationInput(TeamRound::where('name', YouthTeamRoundSeeder::CONFLICT_ROUND)->sole());

        // Mathilde Hay-Schmidt (U19, DS 2113) on Hold 2 has more points than
        // Nanna Reese (DS 1876) on Hold 1, but Youth Players are not compared.
        $this->assertSame([], $this->mutate('validateCrossSquads', $input, 'name'));
    }

    public function test_earlier_rounds_report_no_conflicts_and_place_the_youth_player_differently(): void
    {
        $rounds = TeamRound::where('name', 'like', 'Ungdom - %')->orderBy('round')->get();

        $this->assertSame([1, 2, 3], $rounds->pluck('round')->all());
        $this->assertSame([2025], $rounds->pluck('season_id')->unique()->values()->all());
        $this->assertSame([1], $rounds->pluck('clubhouse_id')->unique()->values()->all());

        foreach ($rounds->take(2) as $round) {
            $input = $this->validationInput($round);
            $this->assertSame([true, true], $this->spotsFulfilled($input), $round->name);
            $this->assertSame([], $this->mutate('validateSquads', $input, 'name'), $round->name);
            $this->assertSame([], $this->mutate('validateCrossSquads', $input, 'name'), $round->name);
        }

        $placements = $rounds->map(fn (TeamRound $round) => SquadMember::query()
            ->where('name', 'Aske Groth Jensen')
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
        return $this->graphQL(
            "mutation (\$input: [ValidateTeam!]!) { {$mutation}(input: \$input) { {$fields} } }",
            ['input' => $input]
        )->assertJsonMissingPath('errors')->json("data.{$mutation}");
    }
}
