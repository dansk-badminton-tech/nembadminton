<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadReserve;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

/**
 * Reserve records (§43) on a Squad: "X for Y", where X comes from a lower Squad of the same lineup.
 */
class SquadReservesTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private const ADD = /** @lang GraphQL */ '
        mutation ($squadId: ID!, $scenarioId: ID, $reserveRefId: String!, $replacedRefId: String!) {
            addSquadReserve(squadId: $squadId, scenarioId: $scenarioId, reserveRefId: $reserveRefId, replacedRefId: $replacedRefId) {
                id
                reserve { refId name gender vintage }
                replaced { refId name gender vintage }
                scenarioId
            }
        }
    ';

    private const DELETE = /** @lang GraphQL */ '
        mutation ($id: ID!) { deleteSquadReserve(id: $id) { id } }
    ';

    private const UPDATE = /** @lang GraphQL */ '
        mutation ($id: ID!, $reserveRefId: String!, $replacedRefId: String!) {
            updateSquadReserve(id: $id, reserveRefId: $reserveRefId, replacedRefId: $replacedRefId) {
                id
                reserve { name }
                replaced { name }
            }
        }
    ';

    private const READ = /** @lang GraphQL */ '
        query ($id: ID!, $scenarioId: ID) {
            teamRound(id: $id) {
                squads { reserves(scenarioId: $scenarioId) { reserve { name } replaced { name } } }
            }
        }
    ';

    private Clubhouse $clubhouse;

    private TeamRound $teamRound;

    /** @var list<Squad> */
    private array $squads;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubhouse = Clubhouse::factory()->create();
        $user = $this->actingAsUserOf($this->clubhouse);
        $this->teamRound = TeamRound::factory()->create(['clubhouse_id' => $this->clubhouse->id, 'user_id' => $user->id]);
        $this->squads = [$this->squad(1), $this->squad(2), $this->squad(3)];

        // Hold 1: Yrsa (1. DS). Hold 2: Xenia (1. DS). Hold 3: Zara (1. DS).
        $this->place($this->squads[0], '1. DS', 'DS', '9001011111', 'Yrsa');
        $this->place($this->squads[1], '1. DS', 'DS', '9001012222', 'Xenia');
        $this->place($this->squads[2], '1. DS', 'DS', '9001013333', 'Zara');
    }

    /** @test */
    public function it_records_a_reserve_from_a_lower_squad_and_reads_it_back(): void
    {
        $this->add($this->squads[0], '9001012222', '9001011111')
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.addSquadReserve.reserve', ['refId' => '9001012222', 'name' => 'Xenia', 'gender' => 'WOMEN', 'vintage' => 'SEN'])
            ->assertJsonPath('data.addSquadReserve.replaced.name', 'Yrsa')
            ->assertJsonPath('data.addSquadReserve.scenarioId', null);

        $this->graphQL(self::READ, ['id' => $this->teamRound->id])->assertJsonPath('data.teamRound.squads', [
            ['reserves' => [['reserve' => ['name' => 'Xenia'], 'replaced' => ['name' => 'Yrsa']]]],
            ['reserves' => []],
            ['reserves' => []],
        ]);
    }

    /** @test */
    public function a_reserve_may_come_from_any_lower_squad(): void
    {
        $this->add($this->squads[0], '9001013333', '9001011111')->assertJsonMissingPath('errors');
    }

    /** @test */
    public function it_rejects_a_reserve_from_the_same_squad(): void
    {
        $this->place($this->squads[0], '2. DS', 'DS', '9001014444', 'Vera');

        $this->assertRejected($this->add($this->squads[0], '9001014444', '9001011111'), 'Reserven skal komme fra et lavere hold.');
    }

    /** @test */
    public function it_rejects_a_reserve_from_a_higher_squad(): void
    {
        $this->assertRejected($this->add($this->squads[1], '9001011111', '9001012222'), 'Reserven skal komme fra et lavere hold.');
    }

    /** @test */
    public function it_rejects_a_reserve_who_isnt_placed(): void
    {
        $this->member('9001015555', 'Una');

        $this->assertRejected($this->add($this->squads[0], '9001015555', '9001011111'), 'Reserven står ikke på et lavere hold.');
    }

    /** @test */
    public function it_rejects_a_replaced_member_who_isnt_on_the_squad(): void
    {
        $this->assertRejected($this->add($this->squads[0], '9001013333', '9001012222'), 'Spilleren, der erstattes, står ikke på holdet.');
    }

    /** @test */
    public function it_rejects_a_second_record_for_the_same_reserve(): void
    {
        $this->place($this->squads[0], '2. DS', 'DS', '9001014444', 'Vera');
        $this->add($this->squads[0], '9001012222', '9001011111')->assertJsonMissingPath('errors');

        $this->assertRejected($this->add($this->squads[0], '9001012222', '9001014444'), 'Xenia er allerede reserve.');
    }

    /** @test */
    public function it_rejects_a_second_record_for_the_same_replaced_member(): void
    {
        $this->add($this->squads[0], '9001012222', '9001011111')->assertJsonMissingPath('errors');

        $this->assertRejected($this->add($this->squads[0], '9001013333', '9001011111'), 'Yrsa er allerede erstattet.');
    }

    /** @test */
    public function it_removes_a_record(): void
    {
        $id = $this->add($this->squads[0], '9001012222', '9001011111')->json('data.addSquadReserve.id');

        $this->graphQL(self::DELETE, ['id' => $id])->assertJsonMissingPath('errors');

        $this->assertDatabaseCount('squad_reserves', 0);
    }

    /** @test */
    public function it_changes_the_reserve_of_a_record_in_place(): void
    {
        $id = $this->add($this->squads[0], '9001012222', '9001011111')->json('data.addSquadReserve.id');

        $this->update($id, '9001013333', '9001011111')
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.updateSquadReserve.id', $id)
            ->assertJsonPath('data.updateSquadReserve.reserve.name', 'Zara')
            ->assertJsonPath('data.updateSquadReserve.replaced.name', 'Yrsa');

        $this->assertSame(['Zara'], $this->reserveNames(0, null));
    }

    /** @test */
    public function it_changes_the_replaced_member_and_keeps_the_same_reserve(): void
    {
        $this->place($this->squads[0], '2. DS', 'DS', '9001014444', 'Vera');
        $id = $this->add($this->squads[0], '9001012222', '9001011111')->json('data.addSquadReserve.id');

        $this->update($id, '9001012222', '9001014444')
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.updateSquadReserve.reserve.name', 'Xenia')
            ->assertJsonPath('data.updateSquadReserve.replaced.name', 'Vera');
    }

    /** @test */
    public function a_rejected_change_keeps_the_record(): void
    {
        $this->place($this->squads[0], '2. DS', 'DS', '9001014444', 'Vera');
        $id = $this->add($this->squads[0], '9001012222', '9001011111')->json('data.addSquadReserve.id');

        $this->update($id, '9001014444', '9001011111')->assertGraphQLErrorMessage('Reserven skal komme fra et lavere hold.');

        $this->assertSame(['Xenia'], $this->reserveNames(0, null));
    }

    /** @test */
    public function a_change_cant_reuse_a_reserve_of_another_record(): void
    {
        $this->place($this->squads[0], '2. DS', 'DS', '9001014444', 'Vera');
        $this->add($this->squads[0], '9001012222', '9001011111')->assertJsonMissingPath('errors');
        $id = $this->add($this->squads[0], '9001013333', '9001014444')->json('data.addSquadReserve.id');

        $this->update($id, '9001012222', '9001014444')->assertGraphQLErrorMessage('Xenia er allerede reserve.');
    }

    /** @test */
    public function a_change_stays_in_the_records_scenario(): void
    {
        $this->add($this->squads[0], '9001012222', '9001011111')->assertJsonMissingPath('errors');
        $draftId = $this->createScenario('Plan B');
        $draftReserve = SquadReserve::query()->where('team_round_scenario_id', $draftId)->sole();

        $this->update($draftReserve->id, '9001013333', '9001011111')->assertJsonMissingPath('errors');

        $this->assertSame(['Xenia'], $this->reserveNames(0, null));
        $this->assertSame(['Zara'], $this->reserveNames(0, $draftId));
    }

    /** @test */
    public function it_denies_changing_a_reserve_in_another_clubhouse_or_without_edit_permission(): void
    {
        $id = $this->add($this->squads[0], '9001012222', '9001011111')->json('data.addSquadReserve.id');

        $this->actingAsUserOf(Clubhouse::factory()->create());
        $this->update($id, '9001013333', '9001011111')->assertGraphQLErrorMessage('This action is unauthorized.');

        $this->actingAsUserOf($this->clubhouse, [Permission::VIEW_TEAMROUNDS]);
        $this->update($id, '9001013333', '9001011111')->assertGraphQLErrorMessage('This action is unauthorized.');

        $this->assertSame('9001012222', SquadReserve::query()->sole()->reserve_ref_id);
    }

    /** @test */
    public function it_denies_adding_a_reserve_in_another_clubhouse(): void
    {
        $this->actingAsUserOf(Clubhouse::factory()->create());

        $this->add($this->squads[0], '9001012222', '9001011111')->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->assertDatabaseCount('squad_reserves', 0);
    }

    /** @test */
    public function it_denies_removing_a_reserve_in_another_clubhouse(): void
    {
        $id = $this->add($this->squads[0], '9001012222', '9001011111')->json('data.addSquadReserve.id');
        $this->actingAsUserOf(Clubhouse::factory()->create());

        $this->graphQL(self::DELETE, ['id' => $id])->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->assertDatabaseCount('squad_reserves', 1);
    }

    /** @test */
    public function it_denies_reading_reserves_of_another_clubhouse_through_authenticated_queries(): void
    {
        $this->add($this->squads[0], '9001012222', '9001011111')->assertJsonMissingPath('errors');
        $this->actingAsUserOf(Clubhouse::factory()->create());

        $this->graphQL(/** @lang GraphQL */ '
            query ($clubhouseId: ID!) {
                teamRounds(clubhouseId: $clubhouseId) { data { squads { reserves { reserve { name } } } } }
            }
        ', ['clubhouseId' => $this->clubhouse->id])->assertGraphQLErrorMessage('This action is unauthorized.');
    }

    /** @test */
    public function a_user_without_edit_permission_cant_change_reserves(): void
    {
        $id = $this->add($this->squads[0], '9001012222', '9001011111')->json('data.addSquadReserve.id');
        $this->actingAsUserOf($this->clubhouse, [Permission::VIEW_TEAMROUNDS]);

        $this->add($this->squads[0], '9001013333', '9001011111')->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->graphQL(self::DELETE, ['id' => $id])->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->assertDatabaseCount('squad_reserves', 1);
    }

    /** @test */
    public function it_rejects_a_scenario_of_another_team_round(): void
    {
        $otherRound = TeamRound::factory()->create(['clubhouse_id' => $this->clubhouse->id, 'user_id' => $this->teamRound->user_id]);
        $scenario = $otherRound->scenarios()->create(['name' => 'Plan B', 'is_official' => false]);

        $this->assertRejected(
            $this->add($this->squads[0], '9001012222', '9001011111', $scenario->id),
            'Scenariet hører ikke til holdrunden.',
        );
    }

    /** @test */
    public function reserves_are_scoped_per_scenario_and_copied_when_a_scenario_is_cloned(): void
    {
        $this->add($this->squads[0], '9001012222', '9001011111')->assertJsonMissingPath('errors');

        $draftId = $this->createScenario('Plan B');
        $this->assertSame(['Xenia'], $this->reserveNames(0, $draftId));

        // A reserve only in the draft doesn't show in the official lineup.
        $draftReserve = SquadReserve::query()->where('team_round_scenario_id', $draftId)->sole();
        $this->graphQL(self::DELETE, ['id' => $draftReserve->id])->assertJsonMissingPath('errors');
        $this->add($this->squads[0], '9001013333', '9001011111', $draftId)->assertJsonMissingPath('errors');

        $this->assertSame(['Xenia'], $this->reserveNames(0, null));
        $this->assertSame(['Zara'], $this->reserveNames(0, $draftId));

        // Cloning the draft copies its reserves.
        $cloneId = $this->createScenario('Plan C', $draftId);
        $this->assertSame(['Zara'], $this->reserveNames(0, $cloneId));
    }

    /** @test */
    public function promotion_carries_the_reserves_over(): void
    {
        $this->add($this->squads[0], '9001012222', '9001011111')->assertJsonMissingPath('errors');
        $draftId = $this->createScenario('Plan B');
        $draftReserve = SquadReserve::query()->where('team_round_scenario_id', $draftId)->sole();
        $this->graphQL(self::DELETE, ['id' => $draftReserve->id])->assertJsonMissingPath('errors');
        $this->add($this->squads[0], '9001013333', '9001011111', $draftId)->assertJsonMissingPath('errors');

        $this->graphQL(/** @lang GraphQL */ 'mutation ($id: ID!) { promoteScenario(scenarioId: $id) { id } }', ['id' => $draftId])
            ->assertJsonMissingPath('errors');

        $this->assertSame(['Zara'], $this->reserveNames(0, null));
        // The outgoing official lineup is kept as a draft, with its reserves.
        $preserved = TeamRoundScenario::query()->where('team_round_id', $this->teamRound->id)->where('is_official', false)->sole();
        $this->assertSame(['Xenia'], $this->reserveNames(0, $preserved->id));
    }

    /**
     * @param  list<Permission>  $permissions
     */
    private function actingAsUserOf(Clubhouse $clubhouse, array $permissions = [Permission::VIEW_TEAMROUNDS, Permission::EDIT_TEAMROUNDS]): User
    {
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        foreach ($permissions as $permission) {
            $user->givePermissionTo($permission->value);
        }
        $this->actingAs($user, 'api');

        return $user;
    }

    private function squad(int $order): Squad
    {
        return Squad::query()->create(['team_round_id' => $this->teamRound->id, 'playerLimit' => 10, 'order' => $order]);
    }

    private function member(string $refId, string $name): Member
    {
        return Member::query()->firstOrCreate(['refId' => $refId], [
            'name' => $name,
            'gender' => 'K',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
    }

    private function place(Squad $squad, string $name, string $category, string $refId, string $memberName): void
    {
        $this->member($refId, $memberName);
        /** @var SquadCategory $slot */
        $slot = $squad->categories()->create(['name' => $name, 'category' => $category]);
        $slot->players()->create(['member_ref_id' => $refId, 'name' => $memberName, 'gender' => 'K']);
    }

    private function add(Squad $squad, string $reserveRefId, string $replacedRefId, ?int $scenarioId = null): TestResponse
    {
        return $this->graphQL(self::ADD, [
            'squadId' => $squad->id,
            'scenarioId' => $scenarioId,
            'reserveRefId' => $reserveRefId,
            'replacedRefId' => $replacedRefId,
        ]);
    }

    private function update(int|string $id, string $reserveRefId, string $replacedRefId): TestResponse
    {
        return $this->graphQL(self::UPDATE, ['id' => $id, 'reserveRefId' => $reserveRefId, 'replacedRefId' => $replacedRefId]);
    }

    private function assertRejected(TestResponse $response, string $message): void
    {
        $response->assertGraphQLErrorMessage($message);
        $this->assertNull($response->json('data.addSquadReserve'));
    }

    private function createScenario(string $name, ?int $sourceScenarioId = null): int
    {
        return (int) $this->graphQL(/** @lang GraphQL */ '
            mutation ($teamRoundId: ID!, $name: String!, $sourceScenarioId: ID) {
                createScenario(teamRoundId: $teamRoundId, name: $name, sourceScenarioId: $sourceScenarioId) { id }
            }
        ', ['teamRoundId' => $this->teamRound->id, 'name' => $name, 'sourceScenarioId' => $sourceScenarioId])
            ->assertJsonMissingPath('errors')
            ->json('data.createScenario.id');
    }

    /**
     * @return list<string>
     */
    private function reserveNames(int $squadIndex, ?int $scenarioId): array
    {
        return array_column(
            $this->graphQL(self::READ, ['id' => $this->teamRound->id, 'scenarioId' => $scenarioId])
                ->json("data.teamRound.squads.$squadIndex.reserves.*.reserve"),
            'name',
        );
    }
}
