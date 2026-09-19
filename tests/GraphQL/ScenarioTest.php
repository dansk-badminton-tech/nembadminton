<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
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
}
