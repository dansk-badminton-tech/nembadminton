<?php

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Enums\RecipientType;
use App\Models\Club;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Point;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\TeamActivityLog;
use App\Models\TeamReceivers;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
    public function it_can_query_team_rounds_with_pagination()
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
                teamRounds(clubhouseId: $clubhouseId, first: 10) {
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
        ])->assertJsonCount(3, 'data.teamRounds.data')
          ->assertJson([
            'data' => [
                'teamRounds' => [
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
            query($teamRoundId: ID!) {
                export(teamRoundId: $teamRoundId)
            }
        ', [
            'teamRoundId' => $teamRound->id,
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
            'team_round_id' => $teamRound->id,
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
                    teamRoundId
                    teamRound {
                        id
                    }
                }
            }
        ', [
            'id' => $teamRound->id
        ])->assertJsonCount(1, 'data.teamNotificationActivity')
          ->assertJson([
            'data' => [
                'teamNotificationActivity' => [
                    [
                        'message' => 'Notification sent',
                        'teamRoundId' => $teamRound->id,
                        'teamRound' => [
                            'id' => $teamRound->id,
                        ],
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_query_team_round_receiver_by_team_round_id()
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
            'team_round_id' => $teamRound->id,
            'emails' => ['test@example.com']
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            query($teamRoundId: ID!) {
                teamRoundReceiver(teamRoundId: $teamRoundId) {
                    emails
                    teamRound {
                        id
                    }
                }
            }
        ', [
            'teamRoundId' => $teamRound->id
        ])->assertJson([
            'data' => [
                'teamRoundReceiver' => [
                    'emails' => ['test@example.com'],
                    'teamRound' => [
                        'id' => $teamRound->id,
                    ],
                ],
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_query_member_search_points_with_team_round_id_filter()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $this->actingAs($user, 'api');

        $club = Club::query()->create([
            'name1' => 'Test Club',
            'badmintonPlayerId' => 12345,
        ]);
        $clubhouse->clubs()->attach($club->id);

        $member = Member::query()->create([
            'refId' => '9001011234',
            'name' => 'Member Search Player',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
        $member->clubs()->attach($club->id);

        Point::query()->create([
            'member_id' => $member->id,
            'points' => 100,
            'position' => 1,
            'category' => 'HS',
            'vintage' => 'SEN',
            'version' => '2024-01-01',
        ]);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 1,
        ]);
        $category = $squad->categories()->create([
            'category' => 'HS',
            'name' => '1. HS',
        ]);
        $category->players()->create([
            'name' => $member->name,
            'gender' => $member->gender,
            'member_ref_id' => $member->refId,
        ]);

        $this->graphQL(/** @lang GraphQL */ '
            query($clubhouse: Int!, $version: Date!, $rankingList: RankingList!, $teamRoundId: String!) {
                memberSearchPoints(
                    clubhouse: $clubhouse,
                    version: $version,
                    rankingList: $rankingList,
                    teamRoundId: $teamRoundId
                ) {
                    data {
                        refId
                    }
                }
            }
        ', [
            'clubhouse' => $clubhouse->id,
            'version' => '2024-01-01',
            'rankingList' => 'MEN_SINGLE',
            'teamRoundId' => $teamRound->id,
        ])->assertJsonCount(0, 'data.memberSearchPoints.data');

        $this->graphQL(/** @lang GraphQL */ '
            query($clubhouse: Int!, $version: Date!, $rankingList: RankingList!) {
                memberSearchPoints(
                    clubhouse: $clubhouse,
                    version: $version,
                    rankingList: $rankingList
                ) {
                    data {
                        refId
                    }
                }
            }
        ', [
            'clubhouse' => $clubhouse->id,
            'version' => '2024-01-01',
            'rankingList' => 'MEN_SINGLE',
        ])->assertJsonCount(1, 'data.memberSearchPoints.data')
          ->assertJsonPath('data.memberSearchPoints.data.0.refId', $member->refId);
    }

    /**
     * @test
     */
    public function it_can_create_cancellation_with_team_round_id()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
        ]);

        $member = Member::query()->create([
            'refId' => '9001015555',
            'name' => 'Cancellation Player',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CancellationInput!) {
                createCancellation(input: $input) {
                    id
                    teamRoundId
                }
            }
        ', [
            'input' => [
                'refId' => $member->refId,
                'teamRoundId' => $teamRound->id,
                'dates' => [
                    'create' => [
                        ['date' => '2024-01-01'],
                    ],
                ],
            ],
        ])->assertJsonPath('data.createCancellation.teamRoundId', $teamRound->id);

        $this->assertDatabaseHas('cancellations', [
            'refId' => $member->refId,
            'team_round_id' => $teamRound->id,
        ]);
    }

    /**
     * @test
     */
    public function it_can_create_a_team_round()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::CREATE_TEAMROUNDS->value);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateTeamRoundInput!) {
                createTeamRound(input: $input) {
                    id
                    name
                }
            }
        ', [
            'input' => [
                'name' => 'New Team Round',
                'gameDate' => '2023-01-01',
                'version' => '2023-01-01'
            ]
        ])->assertJson([
            'data' => [
                'createTeamRound' => [
                    'name' => 'New Team Round'
                ]
            ]
        ]);

        $this->assertDatabaseHas('team_rounds', [
            'name' => 'New Team Round',
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
    }

    /**
     * @test
     */
    public function it_can_update_a_team_round()
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
            mutation($input: UpdateTeamRoundInput!) {
                updateTeamRound(input: $input) {
                    id
                    name
                }
            }
        ', [
            'input' => [
                'id' => $teamRound->id,
                'name' => 'Updated Team Round Name',
                'gameDate' => '2023-02-01',
                'version' => '2023-02-01'
            ]
        ])->assertJson([
            'data' => [
                'updateTeamRound' => [
                    'id' => $teamRound->id,
                    'name' => 'Updated Team Round Name'
                ]
            ]
        ]);

        $this->assertDatabaseHas('team_rounds', [
            'id' => $teamRound->id,
            'name' => 'Updated Team Round Name'
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_a_team_round()
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
                deleteTeamRound(id: $id) {
                    id
                }
            }
        ', [
            'id' => $teamRound->id
        ])->assertJson([
            'data' => [
                'deleteTeamRound' => [
                    'id' => $teamRound->id
                ]
            ]
        ]);

        $this->assertDatabaseMissing('team_rounds', [
            'id' => $teamRound->id
        ]);
    }

    /**
     * @test
     */
    public function it_can_copy_a_team_round()
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
                copyTeamRound(id: $id) {
                    id
                    name
                }
            }
        ', [
            'id' => $teamRound->id
        ])->assertJson([
            'data' => [
                'copyTeamRound' => [
                    'name' => 'Kopi af Original Team'
                ]
            ]
        ]);

        $this->assertDatabaseHas('team_rounds', [
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
                'teamRound' => ['connect' => $teamRound->id],
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
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10
        ]);
    }

    /**
     * @test
     */
    public function it_can_update_a_squad()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 1,
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateSquadInput!) {
                updateSquad(input: $input) {
                    id
                    playerLimit
                    league
                    name
                    playingPlace
                }
            }
        ', [
            'input' => [
                'id' => $squad->id,
                'playerLimit' => 12,
                'league' => 'LIGA',
                'name' => 'Updated Squad',
                'playingPlace' => 'Main Hall',
            ]
        ])->assertJson([
            'data' => [
                'updateSquad' => [
                    'id' => (string)$squad->id,
                    'playerLimit' => 12,
                    'league' => 'LIGA',
                    'name' => 'Updated Squad',
                    'playingPlace' => 'Main Hall',
                ]
            ]
        ]);

        $this->assertDatabaseHas('squads', [
            'id' => $squad->id,
            'playerLimit' => 12,
            'league' => 'LIGA',
            'name' => 'Updated Squad',
            'playing_place' => 'Main Hall',
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_a_squad()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 1,
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                deleteSquad(id: $id) {
                    id
                }
            }
        ', [
            'id' => $squad->id,
        ])->assertJson([
            'data' => [
                'deleteSquad' => [
                    'id' => (string)$squad->id,
                ]
            ]
        ]);

        $this->assertDatabaseMissing('squads', [
            'id' => $squad->id,
        ]);
    }

    /**
     * @test
     */
    public function it_can_move_squad_order_up()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
        $first = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 1,
        ]);
        $second = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 2,
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                moveSquadOrderUp(id: $id) {
                    id
                }
            }
        ', [
            'id' => $second->id,
        ])->assertJson([
            'data' => [
                'moveSquadOrderUp' => [
                    'id' => (string)$second->id,
                ]
            ]
        ]);

        $first->refresh();
        $second->refresh();

        $this->assertSame(2, $first->order);
        $this->assertSame(1, $second->order);
    }

    /**
     * @test
     */
    public function it_can_move_squad_order_down()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
        $first = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 1,
        ]);
        $second = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 2,
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                moveSquadOrderDown(id: $id) {
                    id
                }
            }
        ', [
            'id' => $first->id,
        ])->assertJson([
            'data' => [
                'moveSquadOrderDown' => [
                    'id' => (string)$first->id,
                ]
            ]
        ]);

        $first->refresh();
        $second->refresh();

        $this->assertSame(2, $first->order);
        $this->assertSame(1, $second->order);
    }

    /**
     * @test
     */
    public function it_can_add_a_squad_member_by_ref_id()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 1,
        ]);
        $category = $squad->categories()->create([
            'category' => 'HS',
            'name' => '1. HS',
        ]);

        $member = Member::query()->create([
            'refId' => '9001011234',
            'name' => 'Member Player',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
        Point::query()->create([
            'member_id' => $member->id,
            'points' => 100,
            'position' => 1,
            'category' => 'HS',
            'vintage' => 'SEN',
            'version' => '2024-01-01',
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: AddSquadMemberByRefIdInput!) {
                addSquadMemberByRefId(input: $input) {
                    id
                    refId
                    name
                    points {
                        points
                        category
                    }
                }
            }
        ', [
            'input' => [
                'refId' => $member->refId,
                'categoryId' => $category->id,
                'version' => '2024-01-01',
            ]
        ])->assertJson([
            'data' => [
                'addSquadMemberByRefId' => [
                    'refId' => $member->refId,
                    'name' => 'Member Player',
                    'points' => [
                        [
                            'points' => 100,
                            'category' => 'HS',
                        ]
                    ],
                ]
            ]
        ]);

        $this->assertDatabaseHas('squad_members', [
            'member_ref_id' => $member->refId,
            'squad_category_id' => $category->id,
            'name' => 'Member Player',
        ]);
    }

    /**
     * @test
     */
    public function it_can_update_a_squad_member()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 1,
        ]);
        $category = $squad->categories()->create([
            'category' => 'HS',
            'name' => '1. HS',
        ]);
        Member::query()->create([
            'refId' => '9001011234',
            'name' => 'Member Player',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
        $squadMember = SquadMember::query()->create([
            'member_ref_id' => '9001011234',
            'squad_category_id' => $category->id,
            'name' => 'Member Player',
            'gender' => 'M',
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateSquadMemberInput!) {
                updateSquadMember(input: $input) {
                    id
                    points {
                        points
                        category
                        position
                        corrected_manually
                    }
                }
            }
        ', [
            'input' => [
                'id' => $squadMember->id,
                'points' => [
                    'create' => [
                        [
                            'category' => 'HS',
                            'points' => 77,
                            'position' => 2,
                            'vintage' => 'SEN',
                            'corrected_manually' => true,
                            'version' => '2024-01-01',
                        ],
                    ],
                ],
            ],
        ])->assertJsonFragment([
            'id' => (string)$squadMember->id,
            'points' => 77,
            'category' => 'HS',
            'position' => 2,
            'corrected_manually' => true,
        ]);

        $this->assertDatabaseHas('squad_points', [
            'squad_member_id' => $squadMember->id,
            'points' => 77,
            'category' => 'HS',
            'position' => 2,
            'vintage' => 'SEN',
            'corrected_manually' => true,
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_a_squad_member()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'league' => 'OTHER',
            'order' => 1,
        ]);
        $category = $squad->categories()->create([
            'category' => 'HS',
            'name' => '1. HS',
        ]);
        Member::query()->create([
            'refId' => '9001011234',
            'name' => 'Member Player',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
        $squadMember = SquadMember::query()->create([
            'member_ref_id' => '9001011234',
            'squad_category_id' => $category->id,
            'name' => 'Member Player',
            'gender' => 'M',
        ]);

        $this->actingAs($user, 'api');

        $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                deleteSquadMember(id: $id) {
                    id
                }
            }
        ', [
            'id' => $squadMember->id,
        ])->assertJson([
            'data' => [
                'deleteSquadMember' => [
                    'id' => (string)$squadMember->id,
                ]
            ]
        ]);

        $this->assertDatabaseMissing('squad_members', [
            'id' => $squadMember->id,
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

        $this->assertDatabaseHas('team_activity_logs', [
            'team_round_id' => $teamRound->id,
            'recipient_type' => RecipientType::TEST_SELF->value,
        ]);
    }

    /**
     * @test
     */
    public function it_can_send_team_notification_and_store_receivers()
    {
        Mail::fake();

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
                    'method' => 'MANUAL_EMAILS',
                    'saveEmails' => true,
                    'emails' => ['one@example.com', 'two@example.com'],
                ]
            ]
        ])->assertJson([
            'data' => [
                'sendTeamNotification' => [
                    'id' => $teamRound->id
                ]
            ]
        ]);

        $this->assertDatabaseHas('team_receivers', [
            'team_round_id' => $teamRound->id,
        ]);

        $this->assertDatabaseHas('team_activity_logs', [
            'team_round_id' => $teamRound->id,
            'recipient_type' => RecipientType::MANUAL_EMAILS->value,
        ]);
    }

    /**
     * @test
     */
    public function it_can_update_points_team_round()
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
                updatePointsTeamRound(id: $id, version: $version) {
                    id
                    version
                }
            }
        ', [
            'id' => $teamRound->id,
            'version' => '2023-01-01'
        ])->assertJson([
            'data' => [
                'updatePointsTeamRound' => [
                    'id' => $teamRound->id,
                    'version' => '2023-01-01'
                ]
            ]
        ]);

        $this->assertDatabaseHas('team_rounds', [
            'id' => $teamRound->id,
            'version' => '2023-01-01'
        ]);
    }
}
