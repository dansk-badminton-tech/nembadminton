<?php

namespace Tests\GraphQL;

use App\Models\Club;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class MembersSearchTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private int $clubhouseId;

    /** @test */
    public function it_ignores_a_null_not_on_scenario_filter(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'primary_role_id' => null,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouse: Int!, $notOnScenario: ID) {
                membersSearch(
                    clubhouse: $clubhouse
                    notOnScenario: $notOnScenario
                ) {
                    data { id }
                }
            }
        ', [
            'clubhouse' => $clubhouse->id,
            'notOnScenario' => null,
        ]);

        $response->assertJsonMissingPath('errors')
            ->assertJsonPath('data.membersSearch.data', []);
    }

    /** @test */
    public function it_ignores_a_null_not_on_squad_filter(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'primary_role_id' => null,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouse: Int!, $notOnSquad: String) {
                membersSearch(
                    clubhouse: $clubhouse
                    notOnSquad: $notOnSquad
                ) {
                    data { id }
                }
            }
        ', [
            'clubhouse' => $clubhouse->id,
            'notOnSquad' => null,
        ]);

        $response->assertJsonMissingPath('errors')
            ->assertJsonPath('data.membersSearch.data', []);
    }

    /** @test */
    public function it_excludes_members_with_permanent_afbud_but_keeps_inactive_members(): void
    {
        $this->actingAsClubhouseWithMembers([
            ['refId' => '9001011111', 'name' => 'Active', 'playable' => true, 'inactive' => false],
            ['refId' => '9001012222', 'name' => 'Permanent afbud', 'playable' => false, 'inactive' => false],
            ['refId' => '9001013333', 'name' => 'Inactive', 'playable' => true, 'inactive' => true],
            ['refId' => '9001014444', 'name' => 'Inactive and not playable', 'playable' => false, 'inactive' => true],
        ]);

        $this->assertSame(
            ['Active', 'Inactive', 'Inactive and not playable'],
            $this->searchMemberNames(['excludePermanentCancellations' => true]),
        );
        $this->assertSame(
            ['Active'],
            $this->searchMemberNames(['excludePermanentCancellations' => true, 'inactive' => false]),
        );
        $this->assertSame(
            ['Active', 'Inactive', 'Inactive and not playable', 'Permanent afbud'],
            $this->searchMemberNames(['excludePermanentCancellations' => false]),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     */
    private function actingAsClubhouseWithMembers(array $members): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $club = Club::query()->create(['id' => 10007, 'badmintonPlayerId' => 10007, 'name1' => 'Fixture Club']);
        $clubhouse->clubs()->attach($club);

        foreach ($members as $member) {
            Member::query()->create($member + ['gender' => 'M', 'birthday' => '1990-01-01'])->clubs()->attach($club);
        }

        $this->actingAs(User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'primary_role_id' => null,
        ]), 'api');

        $this->clubhouseId = $clubhouse->id;
    }

    /**
     * @param  array<string, bool>  $filters
     * @return array<int, string>
     */
    private function searchMemberNames(array $filters): array
    {
        $response = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouse: Int!, $inactive: Boolean, $excludePermanentCancellations: Boolean) {
                membersSearch(
                    clubhouse: $clubhouse
                    inactive: $inactive
                    excludePermanentCancellations: $excludePermanentCancellations
                    orderBy: [{column: NAME, order: ASC}]
                ) {
                    data { name }
                }
            }
        ', ['clubhouse' => $this->clubhouseId] + $filters);

        $response->assertJsonMissingPath('errors');

        return array_column($response->json('data.membersSearch.data'), 'name');
    }
}
