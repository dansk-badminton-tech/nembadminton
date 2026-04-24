<?php

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Enums\RecipientType;
use App\Models\Clubhouse;
use App\Models\TeamActivityLog;
use App\Models\TeamReceivers;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class TeamsTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    /**
     * @test
     */
    public function it_can_query_a_team_round()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Test Team'
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            query($id: ID!) {
                teamRound(id: $id) {
                    id
                    name
                }
            }
        ', [
            'id' => $teamRound->id
        ])->assertJson([
            'data' => [
                'teamRound' => [
                    'id' => $teamRound->id,
                    'name' => 'Test Team'
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_query_teams_with_pagination()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        TeamRound::factory()->count(3)->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            query($clubhouseId: ID!) {
                teams(clubhouseId: $clubhouseId, first: 10) {
                    data {
                        id
                        name
                    }
                    paginatorInfo {
                        total
                        count
                    }
                }
            }
        ', [
            'clubhouseId' => $clubhouse->id
        ])->assertJsonCount(3, 'data.teams.data')
          ->assertJson([
            'data' => [
                'teams' => [
                    'paginatorInfo' => [
                        'total' => 3,
                        'count' => 3
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_export_a_team()
    {
        Storage::fake('public');

        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            query($teamId: ID!) {
                export(teamId: $teamId)
            }
        ', [
            'teamId' => $teamRound->id,
        ]);

        $response->assertJsonStructure([
            'data' => [
                'export',
            ],
        ]);

        $files = Storage::disk('public')->allFiles('team-fight/exports');

        $this->assertCount(1, $files);
        $this->assertStringStartsWith("team-fight/exports/{$teamRound->id}-", $files[0]);
        $this->assertStringEndsWith('.csv', $files[0]);
        $this->assertSame(Storage::disk('public')->url($files[0]), $response->json('data.export'));
    }

    /**
     * @test
     */
    public function it_can_query_team_notification_activity()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);

        TeamActivityLog::factory()->create([
            'team_id' => $teamRound->id,
            'user_id' => $user->id,
            'recipient_type' => RecipientType::TEST_SELF,
            'recipient_count' => 1,
            'recipients_summary' => 'Test email to self',
            'message' => 'Notification sent'
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            query($id: ID!) {
                teamNotificationActivity(id: $id) {
                    id
                    message
                }
            }
        ', [
            'id' => $teamRound->id
        ])->assertJsonCount(1, 'data.teamNotificationActivity')
          ->assertJson([
            'data' => [
                'teamNotificationActivity' => [
                    [
                        'message' => 'Notification sent'
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_query_team_receiver()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);

        TeamReceivers::create([
            'team_id' => $teamRound->id,
            'emails' => ['test@example.com']
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            query($team_id: ID!) {
                teamReceiver(team_id: $team_id) {
                    emails
                }
            }
        ', [
            'team_id' => $teamRound->id
        ])->assertJson([
            'data' => [
                'teamReceiver' => [
                    'emails' => ['test@example.com']
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_create_a_team()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::CREATE_TEAMROUNDS->value);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateTeamInput!) {
                createTeam(input: $input) {
                    id
                    name
                }
            }
        ', [
            'input' => [
                'name' => 'New Team',
                'gameDate' => '2023-01-01',
                'version' => '2023-01-01'
            ]
        ])->assertJson([
            'data' => [
                'createTeam' => [
                    'name' => 'New Team'
                ]
            ]
        ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'New Team',
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
    }

    /**
     * @test
     */
    public function it_can_update_a_team()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::EDIT_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Old Name'
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateTeamInput!) {
                updateTeam(input: $input) {
                    id
                    name
                }
            }
        ', [
            'input' => [
                'id' => $teamRound->id,
                'name' => 'Updated Name',
                'gameDate' => '2023-02-01',
                'version' => '2023-02-01'
            ]
        ])->assertJson([
            'data' => [
                'updateTeam' => [
                    'id' => $teamRound->id,
                    'name' => 'Updated Name'
                ]
            ]
        ]);

        $this->assertDatabaseHas('teams', [
            'id' => $teamRound->id,
            'name' => 'Updated Name'
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_a_team()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::DELETE_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                deleteTeam(id: $id) {
                    id
                }
            }
        ', [
            'id' => $teamRound->id
        ])->assertJson([
            'data' => [
                'deleteTeam' => [
                    'id' => $teamRound->id
                ]
            ]
        ]);

        $this->assertDatabaseMissing('teams', [
            'id' => $teamRound->id
        ]);
    }

    /**
     * @test
     */
    public function it_can_copy_a_team()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Original Team'
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                copyTeam(id: $id) {
                    id
                    name
                }
            }
        ', [
            'id' => $teamRound->id
        ])->assertJson([
            'data' => [
                'copyTeam' => [
                    'name' => 'Kopi af Original Team'
                ]
            ]
        ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'Kopi af Original Team',
            'clubhouse_id' => $clubhouse->id
        ]);
    }

    /**
     * @test
     */
    public function it_can_create_a_squad()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateSquadInput!) {
                createSquad(input: $input) {
                    id
                    league
                    playerLimit
                }
            }
        ', [
            'input' => [
                'team' => ['connect' => $teamRound->id],
                'playerLimit' => 10,
                'league' => 'LIGA',
                'categories' => [
                    'create' => [
                        ['category' => 'HS', 'name' => '1. HS']
                    ]
                ]
            ]
        ])->assertJson([
            'data' => [
                'createSquad' => [
                    'league' => 'LIGA',
                    'playerLimit' => 10
                ]
            ]
        ]);

        $this->assertDatabaseHas('squads', [
            'teams_id' => $teamRound->id,
            'playerLimit' => 10
        ]);
    }

    /**
     * @test
     */
    public function it_can_send_team_notification()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::EDIT_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: SendTeamNotificationInput!) {
                sendTeamNotification(input: $input) {
                    id
                }
            }
        ', [
            'input' => [
                'id' => $teamRound->id,
                'type' => 'TEAM_PUBLISH',
                'message' => 'Hello team',
                'receivers' => [
                    'method' => 'TEST_SELF',
                    'saveEmails' => false
                ]
            ]
        ])->assertJson([
            'data' => [
                'sendTeamNotification' => [
                    'id' => $teamRound->id
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_update_points_team()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::EDIT_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!, $version: String!) {
                updatePointsTeam(id: $id, version: $version) {
                    id
                    version
                }
            }
        ', [
            'id' => $teamRound->id,
            'version' => '2023-01-01'
        ])->assertJson([
            'data' => [
                'updatePointsTeam' => [
                    'id' => $teamRound->id,
                    'version' => '2023-01-01'
                ]
            ]
        ]);

        $this->assertDatabaseHas('teams', [
            'id' => $teamRound->id,
            'version' => '2023-01-01'
        ]);
    }
}
