<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class ScenarioTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private function actingClubhouseUser(array $permissions = [Permission::VIEW_TEAMROUNDS, Permission::EDIT_TEAMROUNDS]): array
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
    public function it_creates_a_new_scenario_and_clones_categories_without_duplicating_team_round_or_squads(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser();

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 1',
        ]);

        $squad1 = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Hold 1',
            'playerLimit' => 10,
            'order' => 1,
            'tier' => '1. division',
            'playing_place' => 'Hal A',
            'playing_address' => 'Idrætsvej 1',
            'playing_datetime' => '2026-10-12 10:00:00',
            'external_team_fight_id' => 12345,
        ]);

        $squad2 = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Hold 2',
            'playerLimit' => 10,
            'order' => 2,
            'tier' => '2. division',
            'playing_place' => 'Hal B',
            'playing_address' => 'Idrætsvej 2',
            'playing_datetime' => '2026-10-12 13:00:00',
            'external_team_fight_id' => 12346,
        ]);

        // Create categories on Squad 1 and Squad 2
        $cat1 = SquadCategory::query()->create([
            'squad_id' => $squad1->id,
            'category' => 'HS',
            'name' => '1. HS',
            'team_round_scenario_id' => null,
        ]);

        $cat2 = SquadCategory::query()->create([
            'squad_id' => $squad2->id,
            'category' => 'DS',
            'name' => '1. DS',
            'team_round_scenario_id' => null,
        ]);

        // Create member and player
        $member = Member::query()->create([
            'refId' => '9001011234',
            'name' => 'John Doe',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);

        $player = SquadMember::query()->create([
            'member_ref_id' => $member->refId,
            'squad_category_id' => $cat1->id,
            'name' => 'John Doe',
            'gender' => 'M',
        ]);

        SquadPoint::query()->create([
            'squad_member_id' => $player->id,
            'category' => 'HS',
            'points' => 150,
            'position' => 1,
            'vintage' => 'SEN',
        ]);

        $mutation = /** @lang GraphQL */ '
            mutation CreateScenario($teamRoundId: ID!, $name: String!) {
                createScenario(teamRoundId: $teamRoundId, name: $name) {
                    id
                    name
                    isOfficial
                }
            }
        ';

        $response = $this->graphQL($mutation, [
            'teamRoundId' => $teamRound->id,
            'name' => 'Plan B - Hvis Nikolaj er skadet',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'createScenario' => [
                    'id',
                    'name',
                    'isOfficial',
                ],
            ],
        ]);

        $scenarioData = $response->json('data.createScenario');
        $this->assertEquals('Plan B - Hvis Nikolaj er skadet', $scenarioData['name']);
        $this->assertFalse($scenarioData['isOfficial']);
        $scenarioId = $scenarioData['id'];

        // Assert canonical models are NOT duplicated
        $this->assertEquals(1, TeamRound::count(), 'TeamRound must not be duplicated');
        $this->assertEquals(2, Squad::count(), 'Squads must not be duplicated');

        // Assert scenario was created
        $this->assertDatabaseHas('team_round_scenarios', [
            'id' => $scenarioId,
            'team_round_id' => $teamRound->id,
            'name' => 'Plan B - Hvis Nikolaj er skadet',
            'is_official' => false,
        ]);

        // Assert categories were duplicated for the scenario
        $this->assertEquals(4, SquadCategory::count(), 'Original (2) + Scenario (2) categories');
        $scenarioCategories = SquadCategory::where('team_round_scenario_id', $scenarioId)->get();
        $this->assertCount(2, $scenarioCategories);

        // Check category on squad 1 was duplicated with player & points
        $scenarioCat1 = $scenarioCategories->firstWhere('squad_id', $squad1->id);
        $this->assertNotNull($scenarioCat1);
        $this->assertEquals('HS', $scenarioCat1->category);
        $this->assertEquals('1. HS', $scenarioCat1->name);
        $this->assertCount(1, $scenarioCat1->players);

        $clonedPlayer = $scenarioCat1->players->first();
        $this->assertEquals('9001011234', $clonedPlayer->member_ref_id);
        $this->assertEquals('John Doe', $clonedPlayer->name);
        $this->assertCount(1, $clonedPlayer->points);
        $this->assertEquals(150, $clonedPlayer->points->first()->points);

        // Check category on squad 2 was duplicated
        $scenarioCat2 = $scenarioCategories->firstWhere('squad_id', $squad2->id);
        $this->assertNotNull($scenarioCat2);
        $this->assertEquals('DS', $scenarioCat2->category);
        $this->assertEquals('1. DS', $scenarioCat2->name);
    }

    /** @test */
    public function it_denies_creating_a_scenario_if_user_lacks_edit_permission(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser([Permission::VIEW_TEAMROUNDS]);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 1',
        ]);

        $mutation = /** @lang GraphQL */ '
            mutation CreateScenario($teamRoundId: ID!, $name: String!) {
                createScenario(teamRoundId: $teamRoundId, name: $name) {
                    id
                }
            }
        ';

        $response = $this->graphQL($mutation, [
            'teamRoundId' => $teamRound->id,
            'name' => 'Plan B',
        ]);

        $response->assertGraphQLErrorMessage('This action is unauthorized.');
    }

    /** @test */
    public function it_denies_creating_a_scenario_for_another_clubhouse(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser();
        $otherClubhouse = Clubhouse::factory()->create();
        $otherUser = User::factory()->create(['clubhouse_id' => $otherClubhouse->id]);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $otherClubhouse->id,
            'user_id' => $otherUser->id,
            'name' => 'Runde 1',
        ]);

        $mutation = /** @lang GraphQL */ '
            mutation CreateScenario($teamRoundId: ID!, $name: String!) {
                createScenario(teamRoundId: $teamRoundId, name: $name) {
                    id
                }
            }
        ';

        $response = $this->graphQL($mutation, [
            'teamRoundId' => $teamRound->id,
            'name' => 'Plan B',
        ]);

        $response->assertGraphQLErrorMessage('This action is unauthorized.');
    }

    /** @test */
    public function it_queries_squad_categories_scoped_by_scenario_id_or_defaults_to_official(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser();

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 1',
        ]);

        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Hold 1',
            'playerLimit' => 10,
            'order' => 1,
        ]);

        // Official category (scenario_id = null)
        $officialCat = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'HS',
            'name' => '1. HS',
            'team_round_scenario_id' => null,
        ]);

        // Scenario 1 (Draft)
        $scenario = TeamRoundScenario::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Plan B',
            'is_official' => false,
        ]);

        $draftCat = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'DS',
            'name' => '1. DS',
            'team_round_scenario_id' => $scenario->id,
        ]);

        $query = /** @lang GraphQL */ '
            query GetTeamRound($id: ID!, $scenarioId: ID) {
                teamRound(id: $id) {
                    id
                    squads {
                        id
                        categories(scenarioId: $scenarioId) {
                            id
                            name
                        }
                    }
                }
            }
        ';

        // 1. Query without scenarioId -> returns official categories
        $officialResponse = $this->graphQL($query, ['id' => $teamRound->id]);
        $officialResponse->assertSuccessful();
        $officialCategories = $officialResponse->json('data.teamRound.squads.0.categories');
        $this->assertCount(1, $officialCategories);
        $this->assertEquals('1. HS', $officialCategories[0]['name']);

        // 2. Query with scenarioId -> returns draft categories
        $draftResponse = $this->graphQL($query, ['id' => $teamRound->id, 'scenarioId' => $scenario->id]);
        $draftCategories = $draftResponse->json('data.teamRound.squads.0.categories');
        $this->assertCount(1, $draftCategories);
        $this->assertEquals('1. DS', $draftCategories[0]['name']);

        // 3. Promote scenario to official -> default query now returns promoted scenario categories
        $scenario->update(['is_official' => true]);
        $teamRound->refresh();

        $promotedResponse = $this->graphQL($query, ['id' => $teamRound->id]);
        $promotedCategories = $promotedResponse->json('data.teamRound.squads.0.categories');
        $this->assertCount(1, $promotedCategories);
        $this->assertEquals('1. DS', $promotedCategories[0]['name']);
    }

    /** @test */
    public function it_excludes_draft_scenarios_from_player_visible_to_user_scope(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $manager = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $manager->id,
            'name' => 'Runde 1',
        ]);

        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Hold 1',
            'playerLimit' => 10,
            'order' => 1,
        ]);

        // Official category with no players
        SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'HS',
            'name' => '1. HS',
            'team_round_scenario_id' => null,
        ]);

        // Draft scenario
        $draftScenario = TeamRoundScenario::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Plan B (Draft)',
            'is_official' => false,
        ]);

        $draftCat = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'HS',
            'name' => '1. HS',
            'team_round_scenario_id' => $draftScenario->id,
        ]);

        // Member & Player user
        $member = Member::query()->create([
            'refId' => '9001019999',
            'name' => 'Draft Player',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);

        SquadMember::query()->create([
            'member_ref_id' => $member->refId,
            'squad_category_id' => $draftCat->id,
            'name' => 'Draft Player',
            'gender' => 'M',
        ]);

        $playerUser = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'player_id' => $member->refId,
        ]);
        $playerRole = \Spatie\Permission\Models\Role::where('name', Role::PLAYER->value)->first();
        $playerUser->assignRole(Role::PLAYER->value);
        $playerUser->update(['primary_role_id' => $playerRole->id]);

        $this->actingAs($playerUser, 'api');

        // Player is only in a draft scenario -> TeamRound must NOT be visible
        $visibleRounds = TeamRound::query()->visibleToUser()->get();
        $this->assertFalse($visibleRounds->contains('id', $teamRound->id), 'Draft scenario player must not see round');

        // Once the scenario is promoted to official -> TeamRound becomes visible
        $draftScenario->update(['is_official' => true]);
        $visibleRoundsAfterPromotion = TeamRound::query()->visibleToUser()->get();
        $this->assertTrue($visibleRoundsAfterPromotion->contains('id', $teamRound->id), 'Official scenario player must see round');
    }

    /** @test */
    public function it_isolates_roster_mutations_to_draft_scenario_while_squad_logistics_stay_canonical(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser();

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 1',
        ]);

        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Hold 1',
            'playerLimit' => 10,
            'order' => 1,
            'playing_place' => 'Oprindelig Hal',
        ]);

        // Official categories: HS and DS
        $officialHs = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'HS',
            'name' => '1. HS',
            'team_round_scenario_id' => null,
        ]);

        $officialDs = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'DS',
            'name' => '1. DS',
            'team_round_scenario_id' => null,
        ]);

        // Player 1 in Official HS
        $member1 = Member::query()->create([
            'refId' => '1111111111',
            'name' => 'Spiller 1',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
        $player1 = SquadMember::query()->create([
            'member_ref_id' => $member1->refId,
            'squad_category_id' => $officialHs->id,
            'name' => 'Spiller 1',
            'gender' => 'M',
        ]);

        // Member 2 (to be added only to draft)
        $member2 = Member::query()->create([
            'refId' => '2222222222',
            'name' => 'Spiller 2',
            'gender' => 'M',
            'birthday' => '1992-02-02',
            'playable' => true,
            'inactive' => false,
        ]);

        // 1. Create a draft scenario (Plan B)
        $createScenarioMutation = /** @lang GraphQL */ '
            mutation CreateScenario($teamRoundId: ID!, $name: String!) {
                createScenario(teamRoundId: $teamRoundId, name: $name) {
                    id
                }
            }
        ';
        $scenarioResponse = $this->graphQL($createScenarioMutation, [
            'teamRoundId' => $teamRound->id,
            'name' => 'Plan B',
        ]);
        $scenarioId = $scenarioResponse->json('data.createScenario.id');

        // Retrieve draft categories
        $draftCategories = SquadCategory::query()->where('team_round_scenario_id', $scenarioId)->get();
        $draftHs = $draftCategories->firstWhere('category', 'HS');
        $draftDs = $draftCategories->firstWhere('category', 'DS');
        $draftPlayer1 = $draftHs->players()->first();

        // 2. Add Member 2 to draft DS via addSquadMemberByRefId
        $addPlayerMutation = /** @lang GraphQL */ '
            mutation AddPlayer($refId: String!, $categoryId: Int!, $version: Date!) {
                addSquadMemberByRefId(input: {
                    refId: $refId
                    categoryId: $categoryId
                    version: $version
                }) {
                    id
                    name
                }
            }
        ';
        $addResponse = $this->graphQL($addPlayerMutation, [
            'refId' => $member2->refId,
            'categoryId' => $draftDs->id,
            'version' => '2026-09-01',
        ]);
        $addResponse->assertGraphQLErrorFree();

        // 3. Delete Player 1 from draft HS via deleteSquadMember
        $deletePlayerMutation = /** @lang GraphQL */ '
            mutation DeletePlayer($id: ID!) {
                deleteSquadMember(id: $id) {
                    id
                }
            }
        ';
        $this->graphQL($deletePlayerMutation, [
            'id' => (string) $draftPlayer1->id,
        ])->assertSuccessful();

        // 4. Update Squad match logistics (playingPlace)
        $updateSquadMutation = /** @lang GraphQL */ '
            mutation UpdateSquad($id: ID!, $playingPlace: String) {
                updateSquad(input: {
                    id: $id
                    playingPlace: $playingPlace
                }) {
                    id
                    playingPlace
                }
            }
        ';
        $this->graphQL($updateSquadMutation, [
            'id' => (string) $squad->id,
            'playingPlace' => 'Ny Hal 2',
        ])->assertSuccessful();

        // 5. Query both Official and Draft lineups to verify isolation and canonical logistics
        $query = /** @lang GraphQL */ '
            query GetTeamRound($id: ID!, $scenarioId: ID) {
                teamRound(id: $id) {
                    squads {
                        id
                        playingPlace
                        categories(scenarioId: $scenarioId) {
                            id
                            category
                            name
                            players {
                                id
                                name
                            }
                        }
                    }
                }
            }
        ';

        // Check Draft lineup
        $draftLineup = $this->graphQL($query, ['id' => $teamRound->id, 'scenarioId' => $scenarioId]);
        $draftSquad = $draftLineup->json('data.teamRound.squads.0');
        $this->assertEquals('Ny Hal 2', $draftSquad['playingPlace']);

        $draftHsPlayers = collect($draftSquad['categories'])->firstWhere('category', 'HS')['players'];
        $this->assertEmpty($draftHsPlayers, 'Player 1 was deleted from draft HS');

        $draftDsPlayers = collect($draftSquad['categories'])->firstWhere('category', 'DS')['players'];
        $this->assertCount(1, $draftDsPlayers);
        $this->assertEquals('Spiller 2', $draftDsPlayers[0]['name']);

        // Check Official lineup (untouched roster, but shared playingPlace)
        $officialLineup = $this->graphQL($query, ['id' => $teamRound->id]);
        $officialSquad = $officialLineup->json('data.teamRound.squads.0');
        $this->assertEquals('Ny Hal 2', $officialSquad['playingPlace'], 'Squad logistics reflect on official');

        $officialHsPlayers = collect($officialSquad['categories'])->firstWhere('category', 'HS')['players'];
        $this->assertCount(1, $officialHsPlayers, 'Player 1 must still exist in Official HS');
        $this->assertEquals('Spiller 1', $officialHsPlayers[0]['name']);

        $officialDsPlayers = collect($officialSquad['categories'])->firstWhere('category', 'DS')['players'];
        $this->assertEmpty($officialDsPlayers, 'Spiller 2 must NOT exist in Official DS');
    }

    /** @test */
    public function it_creates_a_new_scenario_from_an_existing_scenario(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser();

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 1',
        ]);

        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Hold 1',
            'playerLimit' => 10,
            'order' => 1,
        ]);

        $catHS = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'HS',
            'name' => '1. HS',
            'team_round_scenario_id' => null,
        ]);

        $catDS = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'DS',
            'name' => '1. DS',
            'team_round_scenario_id' => null,
        ]);

        $member1 = Member::query()->create([
            'name' => 'Spiller 1',
            'gender' => 'M',
            'clubhouse_id' => $clubhouse->id,
            'birthday' => '1990-01-01',
            'refId' => 101,
        ]);
        $member2 = Member::query()->create([
            'name' => 'Spiller 2',
            'gender' => 'K',
            'clubhouse_id' => $clubhouse->id,
            'birthday' => '1992-02-02',
            'refId' => 102,
        ]);
        $member3 = Member::query()->create([
            'name' => 'Spiller 3',
            'gender' => 'K',
            'clubhouse_id' => $clubhouse->id,
            'birthday' => '1994-03-03',
            'refId' => 103,
        ]);

        SquadMember::query()->create([
            'squad_category_id' => $catHS->id,
            'member_ref_id' => $member1->refId,
            'name' => 'Spiller 1',
            'gender' => 'M',
        ]);

        $createScenarioMutation = /** @lang GraphQL */ '
            mutation CreateScenario($teamRoundId: ID!, $name: String!, $sourceScenarioId: ID) {
                createScenario(teamRoundId: $teamRoundId, name: $name, sourceScenarioId: $sourceScenarioId) {
                    id
                    name
                    isOfficial
                }
            }
        ';

        // 1. Create Scenario A from official lineup
        $responseA = $this->graphQL($createScenarioMutation, [
            'teamRoundId' => (string) $teamRound->id,
            'name' => 'Plan A',
        ]);
        $responseA->assertSuccessful();
        $responseA->assertJsonMissing(['errors']);
        $scenarioAId = (int) $responseA->json('data.createScenario.id');

        // Add Spiller 2 into Scenario A's DS
        $scenarioADsCat = SquadCategory::query()
            ->where('squad_id', $squad->id)
            ->where('team_round_scenario_id', $scenarioAId)
            ->where('category', 'DS')
            ->firstOrFail();

        SquadMember::query()->create([
            'squad_category_id' => $scenarioADsCat->id,
            'member_ref_id' => $member2->refId,
            'name' => 'Spiller 2',
            'gender' => 'K',
        ]);

        // 2. Create Scenario B branching from Scenario A
        $responseB = $this->graphQL($createScenarioMutation, [
            'teamRoundId' => (string) $teamRound->id,
            'name' => 'Plan B (afledt af Plan A)',
            'sourceScenarioId' => (string) $scenarioAId,
        ]);
        $responseB->assertSuccessful();
        $responseB->assertJsonMissing(['errors']);
        $scenarioBId = (int) $responseB->json('data.createScenario.id');

        // Verify Scenario B has Spiller 1 (from HS) and Spiller 2 (from DS)
        $scenarioBHsCat = SquadCategory::query()
            ->where('squad_id', $squad->id)
            ->where('team_round_scenario_id', $scenarioBId)
            ->where('category', 'HS')
            ->with('players')
            ->firstOrFail();
        $this->assertCount(1, $scenarioBHsCat->players);
        $this->assertEquals('Spiller 1', $scenarioBHsCat->players->first()->name);

        $scenarioBDsCat = SquadCategory::query()
            ->where('squad_id', $squad->id)
            ->where('team_round_scenario_id', $scenarioBId)
            ->where('category', 'DS')
            ->with('players')
            ->firstOrFail();
        $this->assertCount(1, $scenarioBDsCat->players);
        $this->assertEquals('Spiller 2', $scenarioBDsCat->players->first()->name);

        // 3. Mutate Scenario B (add Spiller 3 to DS) and verify Scenario A is unchanged
        SquadMember::query()->create([
            'squad_category_id' => $scenarioBDsCat->id,
            'member_ref_id' => $member3->refId,
            'name' => 'Spiller 3',
            'gender' => 'K',
        ]);

        $this->assertEquals(2, $scenarioBDsCat->players()->count());
        $this->assertEquals(1, $scenarioADsCat->players()->count(), 'Scenario A must remain untouched when Scenario B is modified');
    }

    /** @test */
    public function it_rejects_creating_scenario_from_source_scenario_belonging_to_another_team_round(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser();

        $teamRound1 = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 1',
        ]);

        $teamRound2 = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 2',
        ]);

        $foreignScenario = TeamRoundScenario::query()->create([
            'team_round_id' => $teamRound2->id,
            'name' => 'Anden runder scenarie',
            'is_official' => false,
        ]);

        $createScenarioMutation = /** @lang GraphQL */ '
            mutation CreateScenario($teamRoundId: ID!, $name: String!, $sourceScenarioId: ID) {
                createScenario(teamRoundId: $teamRoundId, name: $name, sourceScenarioId: $sourceScenarioId) {
                    id
                    name
                }
            }
        ';

        $response = $this->graphQL($createScenarioMutation, [
            'teamRoundId' => (string) $teamRound1->id,
            'name' => 'Ugyldigt scenarie',
            'sourceScenarioId' => (string) $foreignScenario->id,
        ]);

        $this->assertNotNull($response->json('errors'));
    }

    /** @test */
    public function it_promotes_a_draft_scenario_to_official_lineup_and_preserves_previous_official_as_draft(): void
    {
        [$clubhouse, $user] = $this->actingClubhouseUser();

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 1',
        ]);

        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Hold 1',
            'playerLimit' => 10,
            'order' => 1,
        ]);

        $catHS = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'HS',
            'name' => '1. HS',
            'team_round_scenario_id' => null,
        ]);

        $member1 = Member::query()->create([
            'name' => 'Spiller 1',
            'gender' => 'M',
            'clubhouse_id' => $clubhouse->id,
            'birthday' => '1990-01-01',
            'refId' => 101,
        ]);

        $player1 = SquadMember::query()->create([
            'squad_category_id' => $catHS->id,
            'member_ref_id' => $member1->refId,
            'name' => 'Spiller 1',
            'gender' => 'M',
        ]);

        // 1. Create draft Scenario "Plan B"
        $createScenarioMutation = /** @lang GraphQL */ '
            mutation CreateScenario($teamRoundId: ID!, $name: String!) {
                createScenario(teamRoundId: $teamRoundId, name: $name) {
                    id
                    name
                    isOfficial
                }
            }
        ';

        $createResponse = $this->graphQL($createScenarioMutation, [
            'teamRoundId' => (string) $teamRound->id,
            'name' => 'Plan B',
        ]);
        $createResponse->assertSuccessful();
        $planBId = (int) $createResponse->json('data.createScenario.id');

        // 2. Promote Plan B to official
        $promoteScenarioMutation = /** @lang GraphQL */ '
            mutation PromoteScenario($scenarioId: ID!) {
                promoteScenario(scenarioId: $scenarioId) {
                    id
                    name
                    officialScenario {
                        id
                        name
                        isOfficial
                    }
                    scenarios {
                        id
                        name
                        isOfficial
                    }
                }
            }
        ';

        $promoteResponse = $this->graphQL($promoteScenarioMutation, [
            'scenarioId' => (string) $planBId,
        ]);
        $promoteResponse->assertSuccessful();
        $promoteResponse->assertJsonMissing(['errors']);

        $teamRoundData = $promoteResponse->json('data.promoteScenario');
        $this->assertEquals('Runde 1', $teamRoundData['name'], 'TeamRound name is preserved and unchanged on promotion');
        $this->assertEquals((string) $planBId, $teamRoundData['officialScenario']['id']);
        $this->assertTrue($teamRoundData['officialScenario']['isOfficial']);

        // Check scenarios list: Plan B is official, outgoing official is preserved as draft
        $scenarios = collect($teamRoundData['scenarios']);
        $this->assertCount(2, $scenarios);

        $promotedPlanB = $scenarios->firstWhere('id', (string) $planBId);
        $this->assertTrue($promotedPlanB['isOfficial']);

        $preservedOldOfficial = $scenarios->firstWhere('id', '!==', (string) $planBId);
        $this->assertFalse($preservedOldOfficial['isOfficial']);
        $this->assertEquals('Runde 1', $preservedOldOfficial['name'], 'Old official preserved with old round name');

        // Check categories and players preserved non-destructively
        $originalPlayer = SquadMember::query()->find($player1->id);
        $this->assertNotNull($originalPlayer, 'Original player record was not deleted');
        $this->assertEquals($catHS->id, $originalPlayer->squad_category_id);
        $catHS->refresh();
        $this->assertEquals((int) $preservedOldOfficial['id'], $catHS->team_round_scenario_id, 'Original category reassigned to preserved draft scenario');

        // 3. Create another scenario "Plan C" and promote it (swapping between two existing scenario records)
        $createResponseC = $this->graphQL($createScenarioMutation, [
            'teamRoundId' => (string) $teamRound->id,
            'name' => 'Plan C',
        ]);
        $planCId = (int) $createResponseC->json('data.createScenario.id');

        $promoteResponseC = $this->graphQL($promoteScenarioMutation, [
            'scenarioId' => (string) $planCId,
        ]);
        $promoteResponseC->assertSuccessful();
        $promoteResponseC->assertJsonMissing(['errors']);

        $teamRoundDataC = $promoteResponseC->json('data.promoteScenario');
        $this->assertEquals('Runde 1', $teamRoundDataC['name'], 'TeamRound name remains original round name');
        $this->assertEquals((string) $planCId, $teamRoundDataC['officialScenario']['id']);

        $scenariosC = collect($teamRoundDataC['scenarios']);
        $this->assertCount(3, $scenariosC);
        $this->assertTrue($scenariosC->firstWhere('id', (string) $planCId)['isOfficial']);
        $this->assertFalse($scenariosC->firstWhere('id', (string) $planBId)['isOfficial'], 'Plan B swapped back to draft');
        $this->assertFalse($scenariosC->firstWhere('id', (string) $preservedOldOfficial['id'])['isOfficial']);
    }

    /** @test */
    public function it_denies_promoting_scenario_without_permission_or_in_another_clubhouse(): void
    {
        [$clubhouse1, $user1] = $this->actingClubhouseUser();

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse1->id,
            'user_id' => $user1->id,
            'name' => 'Runde 1',
        ]);

        $scenario = TeamRoundScenario::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Plan B',
            'is_official' => false,
        ]);

        $promoteScenarioMutation = /** @lang GraphQL */ '
            mutation PromoteScenario($scenarioId: ID!) {
                promoteScenario(scenarioId: $scenarioId) {
                    id
                }
            }
        ';

        // 1. User from another clubhouse
        [$clubhouse2, $user2] = $this->actingClubhouseUser();
        $response = $this->graphQL($promoteScenarioMutation, ['scenarioId' => (string) $scenario->id]);
        $this->assertNotNull($response->json('errors'));

        // 2. User from same clubhouse without EDIT_TEAMROUNDS
        [$clubhouse3, $user3] = $this->actingClubhouseUser([Permission::VIEW_TEAMROUNDS]);
        $teamRound3 = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse3->id,
            'user_id' => $user3->id,
        ]);
        $scenario3 = TeamRoundScenario::query()->create([
            'team_round_id' => $teamRound3->id,
            'name' => 'Plan B',
            'is_official' => false,
        ]);
        $response3 = $this->graphQL($promoteScenarioMutation, ['scenarioId' => (string) $scenario3->id]);
        $this->assertNotNull($response3->json('errors'));
    }

    /** @test */
    public function it_blocks_notification_dispatch_on_draft_scenarios_and_notifies_only_official_lineup_players(): void
    {
        Notification::fake();

        [$clubhouse, $user] = $this->actingClubhouseUser();

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'name' => 'Runde 1',
        ]);

        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Hold 1',
            'playerLimit' => 10,
            'order' => 1,
        ]);

        $catOfficial = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'HS',
            'name' => '1. HS',
            'team_round_scenario_id' => null,
        ]);

        // Official player with user account
        $officialMember = Member::query()->create([
            'name' => 'Officiel Spiller',
            'gender' => 'M',
            'clubhouse_id' => $clubhouse->id,
            'birthday' => '1990-01-01',
            'refId' => 201,
        ]);
        SquadMember::query()->create([
            'squad_category_id' => $catOfficial->id,
            'member_ref_id' => $officialMember->refId,
            'name' => 'Officiel Spiller',
            'gender' => 'M',
        ]);
        $officialUser = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'player_id' => $officialMember->refId,
        ]);

        // Draft scenario and player
        $draftScenario = TeamRoundScenario::query()->create([
            'team_round_id' => $teamRound->id,
            'name' => 'Plan B',
            'is_official' => false,
        ]);

        $catDraft = SquadCategory::query()->create([
            'squad_id' => $squad->id,
            'category' => 'DS',
            'name' => '1. DS',
            'team_round_scenario_id' => $draftScenario->id,
        ]);

        $draftMember = Member::query()->create([
            'name' => 'Udkast Spiller',
            'gender' => 'K',
            'clubhouse_id' => $clubhouse->id,
            'birthday' => '1992-02-02',
            'refId' => 202,
        ]);
        SquadMember::query()->create([
            'squad_category_id' => $catDraft->id,
            'member_ref_id' => $draftMember->refId,
            'name' => 'Udkast Spiller',
            'gender' => 'K',
        ]);
        $draftUser = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'player_id' => $draftMember->refId,
        ]);

        $notifyMutation = /** @lang GraphQL */ '
            mutation SendNotification($input: SendTeamNotificationInput!) {
                sendTeamNotification(input: $input) {
                    teamRound {
                        id
                    }
                    sentCount
                    skippedPlayers
                }
            }
        ';

        // 1. Try sending notification explicitly targeting the draft scenario -> must fail
        $draftNotificationResponse = $this->graphQL($notifyMutation, [
            'input' => [
                'id' => $teamRound->id,
                'scenarioId' => (string) $draftScenario->id,
                'type' => 'TEAM_PUBLISH',
                'message' => 'Udkast besked',
                'receivers' => [
                    'method' => 'PLATFORM',
                ],
            ],
        ]);
        $this->assertNotNull($draftNotificationResponse->json('errors'), 'Draft notification must be blocked');
        $errorMessage = $draftNotificationResponse->json('errors.0.message');
        $this->assertStringContainsStringIgnoringCase('draft', $errorMessage);

        // 2. Send official notification -> succeeds, only official player notified
        $officialNotificationResponse = $this->graphQL($notifyMutation, [
            'input' => [
                'id' => $teamRound->id,
                'type' => 'TEAM_PUBLISH',
                'message' => 'Officiel besked',
                'receivers' => [
                    'method' => 'PLATFORM',
                ],
            ],
        ]);
        $officialNotificationResponse->assertSuccessful();
        $officialNotificationResponse->assertJsonMissing(['errors']);
        $this->assertEquals(1, $officialNotificationResponse->json('data.sendTeamNotification.sentCount'));

        Notification::assertSentTo($officialUser, \App\Notifications\TeamPublish::class);
        Notification::assertNotSentTo($draftUser, \App\Notifications\TeamPublish::class);
    }
}
