<?php

namespace Tests\GraphQL;

use App\Enums\Role;
use App\Models\Clubhouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

class UpdateMembershipRolesTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private function clubAdminInClubhouse(Clubhouse $clubhouse): User
    {
        $admin = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $admin->assignRole(Role::CLUB_ADMIN->value);
        $admin->save();
        return $admin->refresh();
    }

    private function memberInClubhouse(Clubhouse $clubhouse, array $roleValues = []): User
    {
        $member = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        foreach ($roleValues as $r) {
            $member->assignRole($r);
        }
        $member->save();
        return $member->refresh();
    }

    private function updateMembershipRolesMutation(string $clubhouseId, string $userId, array $roles): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($clubhouseId: ID!, $userId: ID!, $roles: [MemberRole!]!) {
                updateMembershipRoles(clubhouseId: $clubhouseId, userId: $userId, roles: $roles) {
                    id
                    roles { id name }
                    primaryRole { id name }
                }
            }
        ', [
            'clubhouseId' => $clubhouseId,
            'userId' => $userId,
            'roles' => $roles,
        ]);
    }

    /** @test */
    public function it_lets_a_club_admin_sync_a_members_roles(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->clubAdminInClubhouse($clubhouse);
        $target = $this->memberInClubhouse($clubhouse, [Role::PLAYER->value]);

        $this->actingAs($admin, 'api');

        $response = $this->updateMembershipRolesMutation(
            (string) $clubhouse->id,
            (string) $target->id,
            ['COACH', 'PLAYER']
        );

        $response->assertJson([
            'data' => [
                'updateMembershipRoles' => [
                    'id' => (string) $target->id,
                    'roles' => [
                        ['name' => Role::COACH->value],
                        ['name' => Role::PLAYER->value],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_rejects_a_non_club_admin(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $coach = $this->memberInClubhouse($clubhouse, [Role::COACH->value]);
        $target = $this->memberInClubhouse($clubhouse, [Role::PLAYER->value]);

        $this->actingAs($coach, 'api');

        $response = $this->updateMembershipRolesMutation(
            (string) $clubhouse->id,
            (string) $target->id,
            ['COACH']
        );

        $response->assertJsonPath('errors.0.message', 'Only club admins can edit roles');
    }

    /** @test */
    public function it_rejects_self_edit(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->clubAdminInClubhouse($clubhouse);

        $this->actingAs($admin, 'api');

        $response = $this->updateMembershipRolesMutation(
            (string) $clubhouse->id,
            (string) $admin->id,
            ['COACH']
        );

        $response->assertJsonPath('errors.0.message', 'You cannot edit your own roles');
    }

    /** @test */
    public function it_rejects_cross_clubhouse_edit(): void
    {
        $clubhouseA = Clubhouse::factory()->create();
        $clubhouseB = Clubhouse::factory()->create();
        $adminA = $this->clubAdminInClubhouse($clubhouseA);
        $memberB = $this->memberInClubhouse($clubhouseB, [Role::PLAYER->value]);

        $this->actingAs($adminA, 'api');

        $response = $this->updateMembershipRolesMutation(
            (string) $clubhouseA->id,
            (string) $memberB->id,
            ['COACH']
        );

        $response->assertJsonPath('errors.0.message', 'You cannot edit someone elses membership');
    }

    /** @test */
    public function it_resets_primary_role_when_removed_from_role_set(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->clubAdminInClubhouse($clubhouse);
        $target = $this->memberInClubhouse($clubhouse, [Role::PLAYER->value, Role::COACH->value]);

        $playerRole = SpatieRole::where('name', Role::PLAYER->value)->first();
        $target->primary_role_id = $playerRole->id;
        $target->save();
        $target->refresh();

        $this->actingAs($admin, 'api');

        $response = $this->updateMembershipRolesMutation(
            (string) $clubhouse->id,
            (string) $target->id,
            ['COACH']
        );

        $response->assertJsonPath('data.updateMembershipRoles.primaryRole.name', Role::COACH->value);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'primary_role_id' => SpatieRole::where('name', Role::COACH->value)->first()->id,
        ]);
    }
}
