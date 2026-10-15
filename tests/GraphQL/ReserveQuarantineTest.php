<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Season;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadReserve;
use App\Models\TeamRound;
use App\Models\User;
use FlyCompany\TeamFight\GraphQL\Queries\ReserveQuarantineResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

/**
 * Reserve Quarantine (§44): a Reserve Player in the Official Lineup of round N gets a soft tag wherever they are placed
 * in round N+1 of the same Clubhouse and Season.
 */
class ReserveQuarantineTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private const XENIA = '9001012222';

    private Clubhouse $clubhouse;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        ReserveQuarantineResolver::clearCache();

        Season::query()->firstOrCreate(['id' => 2026], ['season_name' => '2026/2027']);
        Season::query()->firstOrCreate(['id' => 2025], ['season_name' => '2025/2026']);
        $this->clubhouse = Clubhouse::factory()->create();
        $this->user = $this->actingAsUserOf($this->clubhouse);
    }

    protected function tearDown(): void
    {
        ReserveQuarantineResolver::clearCache();
        parent::tearDown();
    }

    /** @test */
    public function a_reserve_in_the_official_lineup_of_round_n_is_tagged_in_round_n_plus_1(): void
    {
        $round3 = $this->roundWithReserve(3, 'Runde 3 Senior');
        $round4 = $this->roundWithXeniaPlaced(4);
        $otherRound4 = $this->roundWithXeniaPlaced(4);

        $this->quarantineOf($round4)->assertJsonPath('data.teamRound.squads.0.categories.0.players.0.reserveQuarantine', [
            'teamRoundId' => $round3->id,
            'teamRoundName' => 'Runde 3 Senior',
            'round' => 3,
            'squadPosition' => 1,
            'replacedName' => 'Yrsa',
        ]);
        $this->quarantineOf($otherRound4)->assertJsonPath('data.teamRound.squads.0.categories.0.players.0.reserveQuarantine.round', 3);
    }

    /** @test */
    public function there_is_no_tag_two_rounds_later(): void
    {
        $this->roundWithReserve(3);

        $this->assertNoQuarantine($this->roundWithXeniaPlaced(5));
    }

    /** @test */
    public function there_is_no_tag_in_another_season(): void
    {
        $this->roundWithReserve(3);

        $this->assertNoQuarantine($this->roundWithXeniaPlaced(4, season: 2025));
    }

    /** @test */
    public function there_is_no_tag_in_another_clubhouse(): void
    {
        $this->roundWithReserve(3);
        $otherClubhouse = Clubhouse::factory()->create();
        $this->clubhouse = $otherClubhouse;
        $this->user = $this->actingAsUserOf($otherClubhouse);

        $this->assertNoQuarantine($this->roundWithXeniaPlaced(4));
    }

    /** @test */
    public function there_is_no_tag_when_either_team_round_has_no_round_number(): void
    {
        $this->roundWithReserve(null);
        $this->assertNoQuarantine($this->roundWithXeniaPlaced(1));

        ReserveQuarantineResolver::clearCache();
        $this->roundWithReserve(3);
        $this->assertNoQuarantine($this->roundWithXeniaPlaced(null));
    }

    /** @test */
    public function a_reserve_only_in_a_draft_scenario_gives_no_quarantine(): void
    {
        $round3 = $this->roundWithReserve(3);
        $draft = $round3->scenarios()->create(['name' => 'Plan B', 'is_official' => false]);
        SquadReserve::query()->update(['team_round_scenario_id' => $draft->id]);

        $this->assertNoQuarantine($this->roundWithXeniaPlaced(4));
    }

    /** @test */
    public function a_reserve_in_an_official_scenario_gives_quarantine(): void
    {
        $round3 = $this->roundWithReserve(3);
        $official = $round3->scenarios()->create(['name' => 'Plan B', 'is_official' => true]);
        SquadReserve::query()->update(['team_round_scenario_id' => $official->id]);

        $this->quarantineOf($this->roundWithXeniaPlaced(4))
            ->assertJsonPath('data.teamRound.squads.0.categories.0.players.0.reserveQuarantine.round', 3);
    }

    /** @test */
    public function the_public_query_exposes_no_quarantine_data(): void
    {
        $this->roundWithReserve(3);
        $round4 = $this->roundWithXeniaPlaced(4);

        // Signed out: the field needs a logged-in User.
        $this->app['auth']->forgetGuards();
        $this->quarantineOf($round4)->assertGraphQLErrorMessage('Unauthenticated.');

        // A User of another Clubhouse with the link gets nothing.
        ReserveQuarantineResolver::clearCache();
        $this->actingAsUserOf(Clubhouse::factory()->create());
        $this->assertNoQuarantine($round4);
    }

    /** @test */
    public function the_lookup_is_one_query_however_many_players_are_placed(): void
    {
        $this->roundWithReserve(3);
        $round4 = $this->roundWithXeniaPlaced(4);
        foreach ([['9001013333', 'Zara'], ['9001014444', 'Vera'], ['9001015555', 'Una']] as [$refId, $name]) {
            $this->place($round4->squads()->firstOrFail(), $refId, $name);
        }

        DB::enableQueryLog();
        $this->quarantineOf($round4)->assertJsonMissingPath('errors');
        $reserveQueries = array_filter(DB::getQueryLog(), fn (array $query) => str_contains($query['query'], 'squad_reserves'));

        $this->assertCount(1, $reserveQueries);
    }

    /**
     * A TeamRound whose Hold 1 has Xenia (from Hold 2) as reserve for Yrsa, in its lineup outside any Scenario.
     */
    private function roundWithReserve(?int $round, ?string $name = null): TeamRound
    {
        $teamRound = $this->teamRound($round, 2026, $name);
        $hold1 = Squad::query()->create(['team_round_id' => $teamRound->id, 'playerLimit' => 10, 'order' => 1]);
        $hold2 = Squad::query()->create(['team_round_id' => $teamRound->id, 'playerLimit' => 10, 'order' => 2]);
        $this->place($hold1, '9001011111', 'Yrsa');
        $this->place($hold2, self::XENIA, 'Xenia');
        $hold1->reserves()->create([
            'reserve_ref_id' => self::XENIA, 'reserve_name' => 'Xenia', 'reserve_gender' => 'K',
            'replaced_ref_id' => '9001011111', 'replaced_name' => 'Yrsa', 'replaced_gender' => 'K',
        ]);

        return $teamRound;
    }

    private function roundWithXeniaPlaced(?int $round, int $season = 2026): TeamRound
    {
        $teamRound = $this->teamRound($round, $season);
        $squad = Squad::query()->create(['team_round_id' => $teamRound->id, 'playerLimit' => 10, 'order' => 1]);
        $this->place($squad, self::XENIA, 'Xenia');

        return $teamRound;
    }

    private function teamRound(?int $round, int $season, ?string $name = null): TeamRound
    {
        return TeamRound::factory()->create([
            'clubhouse_id' => $this->clubhouse->id,
            'user_id' => $this->user->id,
            'season_id' => $season,
            'round' => $round,
            'name' => $name,
        ]);
    }

    private function place(Squad $squad, string $refId, string $name): void
    {
        Member::query()->firstOrCreate(['refId' => $refId], [
            'name' => $name, 'gender' => 'K', 'birthday' => '1990-01-01', 'playable' => true, 'inactive' => false,
        ]);
        /** @var SquadCategory $slot */
        $slot = $squad->categories()->create(['name' => '1. DS', 'category' => 'DS']);
        $slot->players()->create(['member_ref_id' => $refId, 'name' => $name, 'gender' => 'K']);
    }

    private function actingAsUserOf(Clubhouse $clubhouse): User
    {
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);
        $this->actingAs($user, 'api');

        return $user;
    }

    private function quarantineOf(TeamRound $teamRound): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            query ($id: ID!) {
                teamRound(id: $id) {
                    squads { categories { players {
                        name
                        reserveQuarantine { teamRoundId teamRoundName round squadPosition replacedName }
                    } } }
                }
            }
        ', ['id' => $teamRound->id]);
    }

    private function assertNoQuarantine(TeamRound $teamRound): void
    {
        $this->quarantineOf($teamRound)
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.teamRound.squads.0.categories.0.players.0.reserveQuarantine', null);
    }
}
