<?php

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Models\Clubhouse;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\TournamentTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class TeamsCrudTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private function actingClubhouseUser(array $permissions): array
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        foreach ($permissions as $permission) {
            $user->givePermissionTo($permission->value);
        }
        $this->actingAs($user, 'api');

        return [$clubhouse, $user];
    }

    /** @test */
    public function it_lists_teams_for_own_clubhouse_only(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::VIEW_TEAMS]);
        $otherClubhouse = Clubhouse::factory()->create();

        Team::factory()->count(2)->create(['clubhouse_id' => $clubhouse->id]);
        Team::factory()->create(['clubhouse_id' => $otherClubhouse->id]);

        $this->graphQL(/** @lang GraphQL */ '
            query($clubhouseId: ID!) {
                teams(clubhouseId: $clubhouseId, first: 10) {
                    data { id name }
                    paginatorInfo { total }
                }
            }
        ', ['clubhouseId' => $clubhouse->id])
            ->assertJsonCount(2, 'data.teams.data')
            ->assertJson(['data' => ['teams' => ['paginatorInfo' => ['total' => 2]]]]);
    }

    /** @test */
    public function it_denies_listing_teams_without_permission(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([]);
        Team::factory()->create(['clubhouse_id' => $clubhouse->id]);

        $this->graphQL(/** @lang GraphQL */ '
            query($clubhouseId: ID!) {
                teams(clubhouseId: $clubhouseId, first: 10) { data { id } }
            }
        ', ['clubhouseId' => $clubhouse->id])
            ->assertGraphQLErrorMessage('This action is unauthorized.');
    }

    /** @test */
    public function it_resolves_tier_relation_on_single_team_query(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::VIEW_TEAMS]);
        $tier = TournamentTier::query()->create(['tier_name' => '1. division']);
        $team = Team::factory()->withTier($tier)->create(['clubhouse_id' => $clubhouse->id]);

        $this->graphQL(/** @lang GraphQL */ '
            query($id: ID!) {
                team(id: $id) {
                    id
                    name
                    tier { id tierName }
                    customTierName
                    groupName
                }
            }
        ', ['id' => $team->id])
            ->assertJson([
                'data' => [
                    'team' => [
                        'id' => (string)$team->id,
                        'name' => $team->name,
                        'tier' => ['id' => (string)$tier->id, 'tierName' => '1. division'],
                        'customTierName' => null,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_creates_team_with_tier_id(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::CREATE_TEAMS]);
        $tier = TournamentTier::query()->create(['tier_name' => 'Serie 1']);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateTeamInput!) {
                createTeam(input: $input) { id name tier { id } customTierName groupName }
            }
        ', ['input' => [
            'name' => '1. holdet',
            'tierId' => (string)$tier->id,
            'groupName' => 'Pulje 1',
        ]])
            ->assertJson([
                'data' => [
                    'createTeam' => [
                        'name' => '1. holdet',
                        'tier' => ['id' => (string)$tier->id],
                        'customTierName' => null,
                        'groupName' => 'Pulje 1',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('teams', [
            'name' => '1. holdet',
            'tier_id' => $tier->id,
            'custom_tier_name' => null,
            'group_name' => 'Pulje 1',
            'clubhouse_id' => $clubhouse->id,
        ]);
    }

    /** @test */
    public function it_creates_team_with_custom_tier_name(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::CREATE_TEAMS]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateTeamInput!) {
                createTeam(input: $input) { id tier { id } customTierName }
            }
        ', ['input' => [
            'name' => '2. holdet',
            'customTierName' => 'Veteran-række',
        ]])
            ->assertJson([
                'data' => [
                    'createTeam' => [
                        'tier' => null,
                        'customTierName' => 'Veteran-række',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('teams', [
            'name' => '2. holdet',
            'tier_id' => null,
            'custom_tier_name' => 'Veteran-række',
            'clubhouse_id' => $clubhouse->id,
        ]);
    }

    /** @test */
    public function it_rejects_team_with_both_tier_id_and_custom_tier_name(): void
    {
        $this->actingClubhouseUser([Permission::CREATE_TEAMS]);
        $tier = TournamentTier::query()->create(['tier_name' => 'Serie 2']);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateTeamInput!) {
                createTeam(input: $input) { id }
            }
        ', ['input' => [
            'name' => 'Konflikt',
            'tierId' => (string)$tier->id,
            'customTierName' => 'Custom',
        ]])
            ->assertGraphQLValidationKeys(['input.tierId']);

        $this->assertDatabaseMissing('teams', ['name' => 'Konflikt']);
    }

    /** @test */
    public function it_requires_name_when_creating_team(): void
    {
        $this->actingClubhouseUser([Permission::CREATE_TEAMS]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateTeamInput!) {
                createTeam(input: $input) { id }
            }
        ', ['input' => ['customTierName' => 'X']])
            ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function it_injects_clubhouse_id_from_authenticated_user_on_create(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::CREATE_TEAMS]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateTeamInput!) {
                createTeam(input: $input) { id }
            }
        ', ['input' => ['name' => 'Auto-clubhouse']])
            ->assertGraphQLErrorFree();

        $this->assertDatabaseHas('teams', [
            'name' => 'Auto-clubhouse',
            'clubhouse_id' => $clubhouse->id,
        ]);
    }

    /** @test */
    public function it_updates_team_name_and_group_name(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::EDIT_TEAMS]);
        $team = Team::factory()->withCustomTier('Gammelt navn')->create([
            'clubhouse_id' => $clubhouse->id,
            'name' => 'Gammel',
            'group_name' => null,
        ]);
        $tier = TournamentTier::query()->create(['tier_name' => '2. division']);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateTeamInput!) {
                updateTeam(input: $input) {
                    id name groupName tier { id } customTierName
                }
            }
        ', ['input' => [
            'id' => (string)$team->id,
            'name' => 'Ny',
            'groupName' => 'Pulje 2',
            'tierId' => (string)$tier->id,
            'customTierName' => null,
        ]])
            ->assertJson([
                'data' => [
                    'updateTeam' => [
                        'name' => 'Ny',
                        'groupName' => 'Pulje 2',
                        'tier' => ['id' => (string)$tier->id],
                        'customTierName' => null,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Ny',
            'group_name' => 'Pulje 2',
            'tier_id' => $tier->id,
            'custom_tier_name' => null,
        ]);
    }

    /** @test */
    public function it_denies_updating_team_in_another_clubhouse(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::EDIT_TEAMS]);
        $otherClubhouse = Clubhouse::factory()->create();
        $team = Team::factory()->create(['clubhouse_id' => $otherClubhouse->id, 'name' => 'Foreign']);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateTeamInput!) {
                updateTeam(input: $input) { id }
            }
        ', ['input' => ['id' => (string)$team->id, 'name' => 'Hacked']])
            ->assertGraphQLErrorMessage('This action is unauthorized.');

        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'Foreign']);
    }

    /** @test */
    public function it_deletes_own_team(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::DELETE_TEAMS]);
        $team = Team::factory()->create(['clubhouse_id' => $clubhouse->id]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                deleteTeam(id: $id) { id }
            }
        ', ['id' => (string)$team->id])
            ->assertGraphQLErrorFree();

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    /** @test */
    public function it_denies_deleting_team_in_another_clubhouse(): void
    {
        [$clubhouse] = $this->actingClubhouseUser([Permission::DELETE_TEAMS]);
        $otherClubhouse = Clubhouse::factory()->create();
        $team = Team::factory()->create(['clubhouse_id' => $otherClubhouse->id]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                deleteTeam(id: $id) { id }
            }
        ', ['id' => (string)$team->id])
            ->assertGraphQLErrorMessage('This action is unauthorized.');

        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }

    /** @test */
    public function it_persists_team_id_when_creating_squad_with_team_reference(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser([
            Permission::CREATE_TEAMROUNDS,
            Permission::EDIT_TEAMROUNDS,
            Permission::VIEW_TEAMROUNDS,
        ]);
        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
        ]);
        $team = Team::factory()->create(['clubhouse_id' => $clubhouse->id]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateSquadInput!) {
                createSquad(input: $input) {
                    id
                    name
                    tier
                    team { id name }
                }
            }
        ', ['input' => [
            'teamRound' => ['connect' => $teamRound->id],
            'playerLimit' => 8,
            'teamId' => (string)$team->id,
            'name' => 'Squad fra hold',
            'tier' => '1. division',
            'categories' => [
                'create' => [
                    ['category' => 'HS', 'name' => 'Single 1'],
                ],
            ],
        ]])
            ->assertJson([
                'data' => [
                    'createSquad' => [
                        'name' => 'Squad fra hold',
                        'tier' => '1. division',
                        'team' => ['id' => (string)$team->id, 'name' => $team->name],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('squads', [
            'team_round_id' => $teamRound->id,
            'team_id' => $team->id,
            'name' => 'Squad fra hold',
            'tier' => '1. division',
        ]);
    }

    /** @test */
    public function it_creates_squad_with_optional_name_and_tier_as_free_text(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser([
            Permission::CREATE_TEAMROUNDS,
            Permission::EDIT_TEAMROUNDS,
            Permission::VIEW_TEAMROUNDS,
        ]);
        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
        ]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateSquadInput!) {
                createSquad(input: $input) {
                    id
                    name
                    tier
                    team { id }
                }
            }
        ', ['input' => [
            'teamRound' => ['connect' => $teamRound->id],
            'playerLimit' => 8,
            'name' => 'Ad-hoc squad',
            'tier' => 'Veteran-række',
            'categories' => [
                'create' => [
                    ['category' => 'HS', 'name' => 'Single 1'],
                ],
            ],
        ]])
            ->assertJson([
                'data' => [
                    'createSquad' => [
                        'name' => 'Ad-hoc squad',
                        'tier' => 'Veteran-række',
                        'team' => null,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('squads', [
            'team_round_id' => $teamRound->id,
            'team_id' => null,
            'name' => 'Ad-hoc squad',
            'tier' => 'Veteran-række',
        ]);
    }

    /** @test */
    public function it_creates_squad_without_name_or_tier(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser([
            Permission::CREATE_TEAMROUNDS,
            Permission::EDIT_TEAMROUNDS,
            Permission::VIEW_TEAMROUNDS,
        ]);
        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
        ]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateSquadInput!) {
                createSquad(input: $input) {
                    id name tier
                }
            }
        ', ['input' => [
            'teamRound' => ['connect' => $teamRound->id],
            'playerLimit' => 8,
            'categories' => [
                'create' => [
                    ['category' => 'HS', 'name' => 'Single 1'],
                ],
            ],
        ]])
            ->assertJson([
                'data' => [
                    'createSquad' => [
                        'name' => null,
                        'tier' => null,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_resolves_squad_team_relation_in_team_round_query(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser([Permission::VIEW_TEAMROUNDS]);
        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
        ]);
        $team = Team::factory()->create(['clubhouse_id' => $clubhouse->id, 'name' => 'Mit hold']);
        Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'team_id' => $team->id,
            'playerLimit' => 8,
            'order' => 1,
            'name' => 'S1',
        ]);

        $this->graphQL(/** @lang GraphQL */ '
            query($id: ID!) {
                teamRound(id: $id) {
                    id
                    squads { id team { id name } }
                }
            }
        ', ['id' => $teamRound->id])
            ->assertJson([
                'data' => [
                    'teamRound' => [
                        'squads' => [
                            ['team' => ['id' => (string)$team->id, 'name' => 'Mit hold']],
                        ],
                    ],
                ],
            ]);
    }
}
