<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\SeasonPhase;
use App\Models\Cancellation;
use App\Models\Member;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\SquadReserve;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Database\Seeders\RealisticSeasonSeeder;
use Database\Seeders\TestingDataSeeder;
use FlyCompany\TeamFight\GraphQL\Queries\ReserveQuarantineResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

/**
 * The seeded 2025/2026 Season of BC37 Amager (see RealisticSeasonSeeder) must look like a real club: valid Official
 * Lineups, every Season Phase, a draft Scenario, Reserve Quarantine and cancellations.
 */
class RealisticSeasonSeedTest extends TestCase
{
    use MakesGraphQLRequests;

    protected function setUp(): void
    {
        parent::setUp();
        ReserveQuarantineResolver::clearCache();

        // TestingDataSeeder needs fresh auto-increment ids (Clubhouse 1, User 1), which
        // RefreshDatabase and DatabaseTruncation don't give after other tests have run.
        $this->artisan('migrate:fresh', ['--seed' => true, '--seeder' => TestingDataSeeder::class]);
        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:fresh');
            // The next RefreshDatabase test must migrate and seed again: this test emptied its seeded tables.
            RefreshDatabaseState::$migrated = false;
        });
    }

    protected function tearDown(): void
    {
        ReserveQuarantineResolver::clearCache();
        parent::tearDown();
    }

    public function test_the_club_has_five_teams_in_the_season(): void
    {
        $teams = Team::query()->where('clubhouse_id', 1)->where('season_id', 2025)->orderBy('id')->get();

        $this->assertSame(
            ['Badmintonligaen', '2. division', 'Danmarksserien', 'Serie 2', 'U17'],
            $teams->map(fn (Team $team) => $team->tierName())->all()
        );
    }

    public function test_every_official_lineup_follows_the_rules(): void
    {
        $rounds = $this->seasonRounds()->filter(fn (TeamRound $round) => $round->round !== RealisticSeasonSeeder::DRAFT_ROUND);

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12], $rounds->pluck('round')->values()->all());

        foreach ($rounds as $round) {
            $input = $this->validationInput($round, null);
            $label = 'Round '.$round->round;

            $this->assertNotContains(false, $this->spotsFulfilled($input), $label);
            $this->assertSame([], $this->mutate('validateSquads', $input, 'name category belowPlayer { name }'), $label);
            $this->assertSame([], $this->mutate('validateCrossSquads', $input, 'name belowPlayer { name category }'), $label);
            $this->assertSame([], $this->mutate('validateReserves', $input, 'type categoryName reserve { name } replaced { name }'), $label);
            $this->assertSame([], $this->graphQL(
                'mutation ($input: [ValidateTeam!]!) { validateTooYoungPlayers(input: $input, seasonStartYear: 2025) { name } }',
                ['input' => $input]
            )->assertJsonMissingPath('errors')->json('data.validateTooYoungPlayers'), $label);
        }
    }

    public function test_the_rounds_cover_every_season_phase(): void
    {
        $phases = $this->seasonRounds()->mapWithKeys(fn (TeamRound $round) => [
            $round->round => $round->squads->map(fn (Squad $squad) => $squad->team->name.': '.SeasonPhase::fromRound($round->round, $squad->effectiveTier())?->value)->all(),
        ]);

        $this->assertCount(5, $phases[7]);
        $this->assertContains('BC37 Amager 1: grundspil', $phases[7]);
        $this->assertSame(['BC37 Amager 1: grundspil'], $phases[9]);
        $this->assertSame(['BC37 Amager 1: slutspil'], $phases[10]);
        $this->assertSame(['BC37 Amager 1: slutspil'], $phases[11]);
        $this->assertSame(['BC37 Amager 2: kvalifikation'], $phases[12]);
    }

    public function test_the_draft_final_has_no_official_lineup_and_one_deliberate_error(): void
    {
        $round = $this->seasonRounds()->firstWhere('round', RealisticSeasonSeeder::DRAFT_ROUND);
        $scenario = $round->scenarios()->sole();

        $this->assertFalse((bool) $scenario->is_official);
        $this->assertSame([], $this->validationInput($round, null)[0]['squad']['categories']);

        // A weaker men's doubles pair placed above a stronger one: reported for both players of the pair.
        $errors = $this->mutate('validateSquads', $this->validationInput($round, $scenario->id), 'category belowPlayer { name }');
        $this->assertCount(2, $errors);
        $this->assertSame(['HD', 'HD'], array_column($errors, 'category'));
        $this->assertSame($errors[0]['belowPlayer'], $errors[1]['belowPlayer']);
    }

    public function test_the_round_3_reserve_is_in_reserve_quarantine_in_round_4(): void
    {
        $this->actingAs(User::query()->findOrFail(1), 'api');
        $reserves = SquadReserve::query()->with('squad.teamRound')->get()
            ->filter(fn (SquadReserve $reserve) => $reserve->squad->teamRound->season_id === 2025);

        $this->assertSame([3, 5], $reserves->map(fn (SquadReserve $reserve) => $reserve->squad->teamRound->round)->sort()->values()->all());
        $reserve = $reserves->first(fn (SquadReserve $reserve) => $reserve->squad->teamRound->round === 3);

        $round4 = $this->seasonRounds()->firstWhere('round', 4);
        $players = collect($this->graphQL(
            'query ($id: ID!) { teamRound(id: $id) { squads { categories { players { refId reserveQuarantine { round squadPosition replacedName } } } } } }',
            ['id' => $round4->id]
        )->assertJsonMissingPath('errors')->json('data.teamRound.squads'))
            ->pluck('categories')->flatten(1)->pluck('players')->flatten(1)
            ->where('refId', $reserve->reserve_ref_id);

        $this->assertNotEmpty($players);
        $this->assertSame(
            ['round' => 3, 'squadPosition' => 2, 'replacedName' => $reserve->replaced_name],
            $players->first()['reserveQuarantine']
        );
    }

    public function test_the_reserves_are_placed_as_played(): void
    {
        $reserves = SquadReserve::query()->with('squad.teamRound.squads')->get()
            ->filter(fn (SquadReserve $reserve) => $reserve->squad->teamRound->season_id === 2025);

        foreach ($reserves as $reserve) {
            $placed = fn (Squad $squad, string $refId) => SquadMember::query()
                ->where('member_ref_id', $refId)
                ->whereHas('category', fn ($query) => $query->where('squad_id', $squad->id))
                ->exists();
            $below = $reserve->squad->teamRound->squads->where('order', '>', $reserve->squad->order);

            $this->assertTrue($placed($reserve->squad, $reserve->reserve_ref_id), 'The reserve plays on the Squad');
            $this->assertFalse($placed($reserve->squad, $reserve->replaced_ref_id), 'The replaced player is off the Squad');
            $this->assertTrue($below->contains(fn (Squad $squad) => $placed($squad, $reserve->reserve_ref_id)), 'The reserve keeps their slot below');
        }
    }

    public function test_cancelled_players_are_not_placed_on_their_dates(): void
    {
        $cancellations = Cancellation::query()->with('dates', 'cancellationCollector')->get();

        $this->assertCount(5, $cancellations);
        $this->assertSame([1], $cancellations->pluck('cancellationCollector.clubhouse_id')->unique()->values()->all());

        $rounds = $this->seasonRounds();
        foreach ($cancellations as $cancellation) {
            $this->assertNotEmpty($cancellation->dates);
            foreach ($cancellation->dates as $date) {
                $day = substr((string) $date->date, 0, 10);
                foreach ($rounds->filter(fn (TeamRound $round) => substr((string) $round->game_date, 0, 10) === $day) as $round) {
                    $placed = SquadMember::query()
                        ->where('member_ref_id', $cancellation->refId)
                        ->whereHas('category.squad', fn ($query) => $query->where('team_round_id', $round->id))
                        ->exists();
                    $this->assertFalse($placed, $cancellation->refId.' is placed on '.$day);
                }
            }
        }
    }

    /**
     * The seeded Season's TeamRounds: those with Squads of a Team, in round order.
     *
     * @return Collection<int, TeamRound>
     */
    private function seasonRounds(): Collection
    {
        return TeamRound::query()
            ->where('clubhouse_id', 1)
            ->where('season_id', 2025)
            ->whereHas('squads', fn ($query) => $query->whereNotNull('team_id'))
            ->with(['squads' => fn ($query) => $query->orderBy('order'), 'squads.team.tier'])
            ->orderBy('round')
            ->get();
    }

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
}
