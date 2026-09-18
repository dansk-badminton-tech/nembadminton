<?php

namespace Tests\GraphQL;

use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SetMemberInactiveOverrideTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private function setOverrideMutation(string $memberId, string $mode): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!, $mode: InactiveOverrideMode!) {
                setMemberInactiveOverride(id: $id, mode: $mode) {
                    id
                    inactive
                    overrideInactive
                }
            }
        ', [
            'id'   => $memberId,
            'mode' => $mode,
        ]);
    }

    /** @test */
    public function it_forces_member_inactive(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id, 'primary_role_id' => null]);
        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Active Player',
            'gender'            => 'M',
            'inactive'          => false,
            'override_inactive' => 'AUTO',
        ]);

        $this->actingAs($user, 'api');

        $response = $this->setOverrideMutation((string) $member->id, 'FORCE_INACTIVE');

        $response->assertJson([
            'data' => [
                'setMemberInactiveOverride' => [
                    'id'               => (string) $member->id,
                    'inactive'         => true,
                    'overrideInactive' => 'FORCE_INACTIVE',
                ],
            ],
        ]);

        $member->refresh();
        $this->assertTrue($member->inactive);
        $this->assertEquals('FORCE_INACTIVE', $member->override_inactive);
    }

    /** @test */
    public function it_forces_member_active(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id, 'primary_role_id' => null]);
        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Inactive Player',
            'gender'            => 'M',
            'inactive'          => true,
            'override_inactive' => 'AUTO',
        ]);

        $this->actingAs($user, 'api');

        $response = $this->setOverrideMutation((string) $member->id, 'FORCE_ACTIVE');

        $response->assertJson([
            'data' => [
                'setMemberInactiveOverride' => [
                    'id'               => (string) $member->id,
                    'inactive'         => false,
                    'overrideInactive' => 'FORCE_ACTIVE',
                ],
            ],
        ]);

        $member->refresh();
        $this->assertFalse($member->inactive);
        $this->assertEquals('FORCE_ACTIVE', $member->override_inactive);
    }

    /** @test */
    public function it_resets_member_override_to_auto(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id, 'primary_role_id' => null]);
        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Overridden Player',
            'gender'            => 'M',
            'inactive'          => true,
            'override_inactive' => 'FORCE_INACTIVE',
        ]);

        $this->actingAs($user, 'api');

        $response = $this->setOverrideMutation((string) $member->id, 'AUTO');

        $response->assertJson([
            'data' => [
                'setMemberInactiveOverride' => [
                    'id'               => (string) $member->id,
                    'inactive'         => true,
                    'overrideInactive' => 'AUTO',
                ],
            ],
        ]);

        $member->refresh();
        $this->assertTrue($member->inactive);
        $this->assertEquals('AUTO', $member->override_inactive);
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Player',
            'gender'            => 'M',
            'inactive'          => false,
            'override_inactive' => 'AUTO',
        ]);

        $response = $this->setOverrideMutation((string) $member->id, 'FORCE_INACTIVE');

        $response->assertGraphQLErrorMessage('Unauthenticated.');
    }

    /** @test */
    public function it_returns_error_when_member_not_found(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id, 'primary_role_id' => null]);

        $this->actingAs($user, 'api');

        $response = $this->setOverrideMutation('999999', 'FORCE_INACTIVE');

        $response->assertGraphQLErrorMessage('Member not found.');
    }

    /** @test */
    public function it_queries_override_inactive_in_members_search(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $club = \App\Models\Club::create([
            'name1'             => 'Test Club',
            'badmintonPlayerId' => 1234,
            'initialized'       => true,
        ]);
        $clubhouse->clubs()->attach($club);

        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Overridden Player',
            'gender'            => 'M',
            'inactive'          => true,
            'override_inactive' => 'FORCE_INACTIVE',
        ]);
        $club->members()->attach($member);

        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id, 'primary_role_id' => null]);
        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouseId: Int!) {
                membersSearch(clubhouse: $clubhouseId, first: 10) {
                    data {
                        id
                        name
                        inactive
                        overrideInactive
                    }
                }
            }
        ', ['clubhouseId' => $clubhouse->id]);

        $response->assertJson([
            'data' => [
                'membersSearch' => [
                    'data' => [
                        [
                            'id'               => (string) $member->id,
                            'name'             => 'Overridden Player',
                            'inactive'         => true,
                            'overrideInactive' => 'FORCE_INACTIVE',
                        ],
                    ],
                ],
            ],
        ]);

        // When filtering by inactive: false, the forced inactive member should not appear
        $responseActive = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouseId: Int!) {
                membersSearch(clubhouse: $clubhouseId, inactive: false, first: 10) {
                    data {
                        id
                    }
                }
            }
        ', ['clubhouseId' => $clubhouse->id]);

        $responseActive->assertJson([
            'data' => [
                'membersSearch' => [
                    'data' => [],
                ],
            ],
        ]);

        // When filtering by inactive: true, the forced inactive member appears
        $responseInactive = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouseId: Int!) {
                membersSearch(clubhouse: $clubhouseId, inactive: true, first: 10) {
                    data {
                        id
                    }
                }
            }
        ', ['clubhouseId' => $clubhouse->id]);

        $responseInactive->assertJson([
            'data' => [
                'membersSearch' => [
                    'data' => [
                        ['id' => (string) $member->id],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_filters_by_effective_inactive_in_member_search_points(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $club = \App\Models\Club::create([
            'name1'             => 'Test Club',
            'badmintonPlayerId' => 1234,
            'initialized'       => true,
        ]);
        $clubhouse->clubs()->attach($club);

        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Force Inactive Player',
            'gender'            => 'K',
            'inactive'          => true,
            'override_inactive' => 'FORCE_INACTIVE',
        ]);
        $club->members()->attach($member);

        \App\Models\Point::create([
            'member_id' => $member->id,
            'points'    => 100,
            'category'  => 'DS',
            'vintage'   => 'SEN',
            'version'   => '2026-09-01',
        ]);

        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id, 'primary_role_id' => null]);
        $this->actingAs($user, 'api');

        // When inactive: false, forced inactive member should not appear
        $responseActiveOnly = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouseId: Int!) {
                memberSearchPoints(clubhouse: $clubhouseId, version: "2026-09-01", rankingList: WOMEN_SINGLE, inactive: false) {
                    data {
                        id
                    }
                }
            }
        ', ['clubhouseId' => $clubhouse->id]);

        $responseActiveOnly->assertJson([
            'data' => [
                'memberSearchPoints' => [
                    'data' => [],
                ],
            ],
        ]);

        // When inactive: true, forced inactive member appears
        $responseInactive = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouseId: Int!) {
                memberSearchPoints(clubhouse: $clubhouseId, version: "2026-09-01", rankingList: WOMEN_SINGLE, inactive: true) {
                    data {
                        id
                        name
                        inactive
                        overrideInactive
                    }
                }
            }
        ', ['clubhouseId' => $clubhouse->id]);

        $responseInactive->assertJson([
            'data' => [
                'memberSearchPoints' => [
                    'data' => [
                        [
                            'id'               => (string) $member->id,
                            'name'             => 'Force Inactive Player',
                            'inactive'         => true,
                            'overrideInactive' => 'FORCE_INACTIVE',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
