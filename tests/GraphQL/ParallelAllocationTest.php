<?php

namespace Tests\GraphQL;

use App\Models\Club;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Point;
use App\Models\Season;
use App\Models\Squad;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class ParallelAllocationTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    /**
     * @test
     */
    public function it_returns_parallel_allocation_for_player_assigned_in_matching_round_in_same_season_and_clubhouse()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $this->actingAs($user, 'api');

        $club = Club::query()->create([
            'name1' => 'Test Club',
            'badmintonPlayerId' => 88888,
        ]);
        $clubhouse->clubs()->attach($club->id);

        $season = Season::query()->firstOrCreate([
            'id' => 2024,
        ], [
            'season_name' => '2024/2025',
        ]);

        // Player A: allocated in parallel round
        $memberA = Member::query()->create([
            'refId' => '9101011111',
            'name' => 'Alice Test',
            'gender' => 'W',
            'birthday' => '1991-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
        $memberA->clubs()->attach($club->id);
        Point::query()->create([
            'member_id' => $memberA->id,
            'points' => 200,
            'position' => 1,
            'category' => 'DS',
            'vintage' => 'SEN',
            'version' => '2024-01-01',
        ]);

        // Player B: free / unallocated
        $memberB = Member::query()->create([
            'refId' => '9202022222',
            'name' => 'Bob Test',
            'gender' => 'M',
            'birthday' => '1992-02-02',
            'playable' => true,
            'inactive' => false,
        ]);
        $memberB->clubs()->attach($club->id);
        Point::query()->create([
            'member_id' => $memberB->id,
            'points' => 150,
            'position' => 2,
            'category' => 'HS',
            'vintage' => 'SEN',
            'version' => '2024-01-01',
        ]);

        // Active TeamRound (Round 1)
        $activeTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season->id,
            'round' => 1,
            'name' => '1. Holdrunde Senior',
        ]);

        // Parallel TeamRound (Round 1, same season, same clubhouse)
        $parallelTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season->id,
            'round' => 1,
            'name' => '1. Holdrunde Veteran',
        ]);

        $parallelSquad = Squad::query()->create([
            'team_round_id' => $parallelTeamRound->id,
            'playerLimit' => 10,
            'order' => 1,
            'name' => '1. Veteranhold',
        ]);
        $parallelCategory = $parallelSquad->categories()->create([
            'category' => 'DS',
            'name' => '1. DS',
        ]);
        $parallelCategory->players()->create([
            'name' => $memberA->name,
            'gender' => $memberA->gender,
            'member_ref_id' => $memberA->refId,
        ]);

        $query = /** @lang GraphQL */ '
            query($clubhouse: Int!, $version: Date!, $rankingList: RankingList!, $teamRoundId: String!, $activeRoundId: String) {
                memberSearchPoints(
                    clubhouse: $clubhouse,
                    version: $version,
                    rankingList: $rankingList,
                    teamRoundId: $teamRoundId
                ) {
                    data {
                        refId
                        name
                        parallelAllocation(teamRoundId: $activeRoundId) {
                            teamRoundId
                            teamRoundName
                            squadName
                            squadOrder
                            categoryName
                        }
                    }
                }
            }
        ';

        $response = $this->graphQL($query, [
            'clubhouse' => $clubhouse->id,
            'version' => '2024-01-01',
            'rankingList' => 'ALL_LEVEL',
            'teamRoundId' => $activeTeamRound->id,
            'activeRoundId' => $activeTeamRound->id,
        ]);
        if (isset($response->json()['errors'])) {
            $this->fail(json_encode($response->json()['errors']));
        }

        $response->assertJsonPath('data.memberSearchPoints.data.0.refId', $memberA->refId);
        $response->assertJsonPath('data.memberSearchPoints.data.0.parallelAllocation.teamRoundId', $parallelTeamRound->id);
        $response->assertJsonPath('data.memberSearchPoints.data.0.parallelAllocation.teamRoundName', '1. Holdrunde Veteran');
        $response->assertJsonPath('data.memberSearchPoints.data.0.parallelAllocation.squadName', '1. Veteranhold');
        $response->assertJsonPath('data.memberSearchPoints.data.0.parallelAllocation.squadOrder', 1);
        $response->assertJsonPath('data.memberSearchPoints.data.0.parallelAllocation.categoryName', '1. DS');

        $response->assertJsonPath('data.memberSearchPoints.data.1.refId', $memberB->refId);
        $response->assertJsonPath('data.memberSearchPoints.data.1.parallelAllocation', null);
    }

    /**
     * @test
     */
    public function it_returns_null_when_round_or_season_differs()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $this->actingAs($user, 'api');

        $club = Club::query()->create([
            'name1' => 'Test Club',
            'badmintonPlayerId' => 88889,
        ]);
        $clubhouse->clubs()->attach($club->id);

        $season1 = Season::query()->firstOrCreate([
            'id' => 2024,
        ], [
            'season_name' => '2024/2025',
        ]);
        $season2 = Season::query()->firstOrCreate([
            'id' => 2025,
        ], [
            'season_name' => '2025/2026',
        ]);

        $member = Member::query()->create([
            'refId' => '9303033333',
            'name' => 'Charlie Test',
            'gender' => 'M',
            'birthday' => '1993-03-03',
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

        $activeTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season1->id,
            'round' => 1,
            'name' => 'Runde 1',
        ]);

        // Different round number (Round 2)
        $diffRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season1->id,
            'round' => 2,
            'name' => 'Runde 2',
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $diffRound->id,
            'playerLimit' => 10,
            'order' => 1,
        ]);
        $category = $squad->categories()->create(['category' => 'HS', 'name' => '1. HS']);
        $category->players()->create([
            'name' => $member->name,
            'gender' => $member->gender,
            'member_ref_id' => $member->refId,
        ]);

        $query = /** @lang GraphQL */ '
            query($clubhouse: Int!, $version: Date!, $rankingList: RankingList!, $activeRoundId: String) {
                memberSearchPoints(
                    clubhouse: $clubhouse,
                    version: $version,
                    rankingList: $rankingList
                ) {
                    data {
                        refId
                        parallelAllocation(teamRoundId: $activeRoundId) {
                            teamRoundId
                        }
                    }
                }
            }
        ';

        $response = $this->graphQL($query, [
            'clubhouse' => $clubhouse->id,
            'version' => '2024-01-01',
            'rankingList' => 'ALL_LEVEL',
            'activeRoundId' => $activeTeamRound->id,
        ]);

        $response->assertJsonPath('data.memberSearchPoints.data.0.parallelAllocation', null);

        // Different season (Season 2, Round 1)
        $diffSeason = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season2->id,
            'round' => 1,
            'name' => 'Season 2 Round 1',
        ]);
        $squadSeason = Squad::query()->create([
            'team_round_id' => $diffSeason->id,
            'playerLimit' => 10,
            'order' => 1,
        ]);
        $categorySeason = $squadSeason->categories()->create(['category' => 'HS', 'name' => '1. HS']);
        $categorySeason->players()->create([
            'name' => $member->name,
            'gender' => $member->gender,
            'member_ref_id' => $member->refId,
        ]);

        \FlyCompany\TeamFight\GraphQL\Queries\ParallelAllocationResolver::clearCache();

        $responseSeason = $this->graphQL($query, [
            'clubhouse' => $clubhouse->id,
            'version' => '2024-01-01',
            'rankingList' => 'ALL_LEVEL',
            'activeRoundId' => $activeTeamRound->id,
        ]);

        $responseSeason->assertJsonPath('data.memberSearchPoints.data.0.parallelAllocation', null);
    }

    /**
     * @test
     */
    public function it_returns_null_when_active_round_has_no_round_or_season_set()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $this->actingAs($user, 'api');

        $club = Club::query()->create([
            'name1' => 'Test Club',
            'badmintonPlayerId' => 88890,
        ]);
        $clubhouse->clubs()->attach($club->id);

        $member = Member::query()->create([
            'refId' => '9404044444',
            'name' => 'Dana Test',
            'gender' => 'W',
            'birthday' => '1994-04-04',
            'playable' => true,
            'inactive' => false,
        ]);
        $member->clubs()->attach($club->id);
        Point::query()->create([
            'member_id' => $member->id,
            'points' => 100,
            'position' => 1,
            'category' => 'DS',
            'vintage' => 'SEN',
            'version' => '2024-01-01',
        ]);

        // Active round without round number
        $activeTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => null,
            'round' => null,
            'name' => 'Unconfigured Round',
        ]);

        $query = /** @lang GraphQL */ '
            query($clubhouse: Int!, $version: Date!, $rankingList: RankingList!, $activeRoundId: String) {
                memberSearchPoints(
                    clubhouse: $clubhouse,
                    version: $version,
                    rankingList: $rankingList
                ) {
                    data {
                        refId
                        parallelAllocation(teamRoundId: $activeRoundId) {
                            teamRoundId
                        }
                    }
                }
            }
        ';

        $response = $this->graphQL($query, [
            'clubhouse' => $clubhouse->id,
            'version' => '2024-01-01',
            'rankingList' => 'ALL_LEVEL',
            'activeRoundId' => $activeTeamRound->id,
        ]);

        $response->assertJsonPath('data.memberSearchPoints.data.0.parallelAllocation', null);
    }

    /**
     * @test
     */
    public function it_executes_batch_lookup_without_n_plus_one_queries()
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $this->actingAs($user, 'api');

        $club = Club::query()->create([
            'name1' => 'Test Club',
            'badmintonPlayerId' => 88891,
        ]);
        $clubhouse->clubs()->attach($club->id);

        $season = Season::query()->firstOrCreate([
            'id' => 2024,
        ], [
            'season_name' => '2024/2025',
        ]);

        // Create 10 members
        for ($i = 1; $i <= 10; $i++) {
            $m = Member::query()->create([
                'refId' => sprintf('950505%04d', $i),
                'name' => 'Player ' . $i,
                'gender' => 'M',
                'birthday' => '1995-05-05',
                'playable' => true,
                'inactive' => false,
            ]);
            $m->clubs()->attach($club->id);
            Point::query()->create([
                'member_id' => $m->id,
                'points' => 100 + $i,
                'position' => $i,
                'category' => 'HS',
                'vintage' => 'SEN',
                'version' => '2024-01-01',
            ]);
        }

        $activeTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season->id,
            'round' => 1,
            'name' => 'Holdrunde 1',
        ]);

        \FlyCompany\TeamFight\GraphQL\Queries\ParallelAllocationResolver::clearCache();

        $queryCount = 0;
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        $query = /** @lang GraphQL */ '
            query($clubhouse: Int!, $version: Date!, $rankingList: RankingList!, $activeRoundId: String) {
                memberSearchPoints(
                    clubhouse: $clubhouse,
                    version: $version,
                    rankingList: $rankingList,
                    first: 10
                ) {
                    data {
                        refId
                        parallelAllocation(teamRoundId: $activeRoundId) {
                            teamRoundId
                        }
                    }
                }
            }
        ';

        $response = $this->graphQL($query, [
            'clubhouse' => $clubhouse->id,
            'version' => '2024-01-01',
            'rankingList' => 'ALL_LEVEL',
            'activeRoundId' => $activeTeamRound->id,
        ]);

        $response->assertJsonCount(10, 'data.memberSearchPoints.data');

        // With 10 members, resolving parallelAllocation should only add 2 database queries:
        // 1 to fetch active TeamRound, 1 to fetch matching parallel allocations.
        // Total queries for the whole GraphQL request should be well bounded and not scale with 10.
        $this->assertLessThan(15, $queryCount);
    }

    /**
     * @test
     */
    public function it_resolves_parallel_allocation_on_squad_member_in_team_round_query(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
        ]);
        $this->actingAs($user);

        $season = Season::query()->firstOrCreate([
            'id' => 2026,
        ], [
            'season_name' => '2025/2026',
        ]);

        // Active TeamRound (Round 1)
        $activeTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season->id,
            'round' => 1,
            'name' => 'Senior 1 - Runde 1',
        ]);
        $activeSquad = Squad::query()->create([
            'team_round_id' => $activeTeamRound->id,
            'playerLimit' => 10,
            'order' => 1,
            'name' => '1. Hold',
        ]);
        $activeCategory = $activeSquad->categories()->create([
            'name' => '1. HS',
            'category' => 'HS',
        ]);

        // Parallel TeamRound (also Round 1 in same season & clubhouse)
        $parallelTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season->id,
            'round' => 1,
            'name' => 'Senior 2 - Runde 1',
        ]);
        $parallelSquad = Squad::query()->create([
            'team_round_id' => $parallelTeamRound->id,
            'playerLimit' => 10,
            'order' => 1,
            'name' => '2. Hold',
        ]);
        $parallelCategory = $parallelSquad->categories()->create([
            'name' => '2. HS',
            'category' => 'HS',
        ]);

        // Member A: assigned in parallel round
        $memberA = Member::query()->create([
            'name' => 'Spiller A',
            'refId' => 'ref-parallel-A',
            'gender' => 'M',
        ]);
        $parallelCategory->players()->create([
            'member_ref_id' => $memberA->refId,
            'name' => $memberA->name,
            'gender' => 'MEN',
        ]);

        // Member B: not assigned in parallel round
        $memberB = Member::query()->create([
            'name' => 'Spiller B',
            'refId' => 'ref-parallel-B',
            'gender' => 'M',
        ]);

        // Assign both into the active round
        $activeCategory->players()->create([
            'member_ref_id' => $memberA->refId,
            'name' => $memberA->name,
            'gender' => 'MEN',
        ]);
        $activeCategory->players()->create([
            'member_ref_id' => $memberB->refId,
            'name' => $memberB->name,
            'gender' => 'MEN',
        ]);

        \FlyCompany\TeamFight\GraphQL\Queries\ParallelAllocationResolver::clearCache();

        $query = /** @lang GraphQL */ '
            query($id: ID!) {
                teamRound(id: $id) {
                    id
                    squads {
                        id
                        categories {
                            id
                            players {
                                id
                                refId
                                parallelAllocation {
                                    teamRoundId
                                    teamRoundName
                                    squadName
                                    squadOrder
                                    categoryName
                                }
                            }
                        }
                    }
                }
            }
        ';

        $response = $this->graphQL($query, [
            'id' => $activeTeamRound->id,
        ]);

        if (isset($response->json()['errors'])) {
            $this->fail(json_encode($response->json()['errors']));
        }

        $players = $response->json('data.teamRound.squads.0.categories.0.players');
        $this->assertCount(2, $players);

        $playerA = collect($players)->firstWhere('refId', $memberA->refId);
        $playerB = collect($players)->firstWhere('refId', $memberB->refId);

        $this->assertNotNull($playerA);
        $this->assertNotNull($playerA['parallelAllocation']);
        $this->assertEquals((string)$parallelTeamRound->id, $playerA['parallelAllocation']['teamRoundId']);
        $this->assertEquals('Senior 2 - Runde 1', $playerA['parallelAllocation']['teamRoundName']);
        $this->assertEquals('2. Hold', $playerA['parallelAllocation']['squadName']);
        $this->assertEquals(1, $playerA['parallelAllocation']['squadOrder']);
        $this->assertEquals('2. HS', $playerA['parallelAllocation']['categoryName']);

        $this->assertNotNull($playerB);
        $this->assertNull($playerB['parallelAllocation']);
    }

    /**
     * @test
     */
    public function it_executes_batch_lookup_without_n_plus_one_queries_on_team_round(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
        ]);
        $this->actingAs($user);

        $season = Season::query()->firstOrCreate([
            'id' => 2026,
        ], [
            'season_name' => '2025/2026',
        ]);

        $activeTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season->id,
            'round' => 1,
            'name' => 'Senior 1 - Runde 1',
        ]);
        $activeSquad = Squad::query()->create([
            'team_round_id' => $activeTeamRound->id,
            'playerLimit' => 10,
            'order' => 1,
            'name' => '1. Hold',
        ]);
        $activeCategory = $activeSquad->categories()->create([
            'name' => 'HS',
            'category' => 'HS',
        ]);

        $parallelTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season->id,
            'round' => 1,
            'name' => 'Senior 2 - Runde 1',
        ]);
        $parallelSquad = Squad::query()->create([
            'team_round_id' => $parallelTeamRound->id,
            'playerLimit' => 10,
            'order' => 1,
            'name' => '2. Hold',
        ]);
        $parallelCategory = $parallelSquad->categories()->create([
            'name' => 'HS',
            'category' => 'HS',
        ]);

        // Create 10 players assigned to both active and parallel rounds
        for ($i = 1; $i <= 10; $i++) {
            $m = Member::query()->create([
                'name' => "Player $i",
                'refId' => "ref-$i",
                'gender' => 'M',
            ]);
            $parallelCategory->players()->create([
                'member_ref_id' => $m->refId,
                'name' => $m->name,
                'gender' => 'MEN',
            ]);
            $activeCategory->players()->create([
                'member_ref_id' => $m->refId,
                'name' => $m->name,
                'gender' => 'MEN',
            ]);
        }

        \FlyCompany\TeamFight\GraphQL\Queries\ParallelAllocationResolver::clearCache();

        $queryCount = 0;
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        $query = /** @lang GraphQL */ '
            query($id: ID!) {
                teamRound(id: $id) {
                    id
                    squads {
                        id
                        categories {
                            id
                            players {
                                id
                                refId
                                parallelAllocation {
                                    teamRoundId
                                }
                            }
                        }
                    }
                }
            }
        ';

        $response = $this->graphQL($query, [
            'id' => $activeTeamRound->id,
        ]);

        if (isset($response->json()['errors'])) {
            $this->fail(json_encode($response->json()['errors']));
        }

        $players = $response->json('data.teamRound.squads.0.categories.0.players');
        $this->assertCount(10, $players);

        // Fetching teamRound with 10 squad players resolving parallelAllocation
        // must use memoization and not issue 10 separate queries.
        // Queries should be:
        // 1: teamRound
        // 2: squads
        // 3: squad_categories
        // 4: squad_members
        // 5: squad_categories + squads join (cached categoryToRound)
        // 6: active round lookup
        // 7: parallel squad members lookup
        // Total = 7 queries << 15.
        $this->assertLessThan(15, $queryCount);
    }
}
