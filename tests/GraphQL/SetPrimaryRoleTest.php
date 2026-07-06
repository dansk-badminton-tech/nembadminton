<?php

namespace Tests\GraphQL;

use App\Enums\Role;
use App\Models\Clubhouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SetPrimaryRoleTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private function userWithRoles(array $roleValues, ?int $primaryRoleId = null): User
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        foreach ($roleValues as $roleValue) {
            $user->assignRole($roleValue);
        }
        if ($primaryRoleId !== null) {
            $user->primary_role_id = $primaryRoleId;
            $user->save();
        }
        return $user->refresh();
    }

    private function setPrimaryRoleMutation(string $roleId): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($roleId: ID!) {
                setPrimaryRole(roleId: $roleId) {
                    id
                    primaryRole {
                        id
                        name
                    }
                }
            }
        ', ['roleId' => $roleId]);
    }

    /** @test */
    public function it_sets_primary_role_to_an_assigned_role(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->assignRole(Role::PLAYER->value);
        $user->assignRole(Role::COACH->value);
        $user->save();
        $user->refresh();

        $playerRoleId = \Spatie\Permission\Models\Role::where('name', Role::PLAYER->value)->first()->id;

        $this->actingAs($user, 'api');

        $response = $this->setPrimaryRoleMutation((string) $playerRoleId);

        $response->assertJson([
            'data' => [
                'setPrimaryRole' => [
                    'id' => (string) $user->id,
                    'primaryRole' => [
                        'id' => (string) $playerRoleId,
                        'name' => Role::PLAYER->value,
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'primary_role_id' => $playerRoleId,
        ]);
    }

    /** @test */
    public function it_rejects_setting_a_role_the_user_does_not_have(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->assignRole(Role::PLAYER->value);
        $user->save();
        $user->refresh();

        $coachRoleId = \Spatie\Permission\Models\Role::where('name', Role::COACH->value)->first()->id;

        $this->actingAs($user, 'api');

        $response = $this->setPrimaryRoleMutation((string) $coachRoleId);

        $response->assertJsonPath('errors.0.message', 'You do not have this role.');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'primary_role_id' => $coachRoleId,
        ]);
    }

    /** @test */
    public function it_rejects_a_non_existent_role_id(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->assignRole(Role::PLAYER->value);
        $user->save();
        $user->refresh();

        $this->actingAs($user, 'api');

        $response = $this->setPrimaryRoleMutation('999999');

        $response->assertJsonPath('errors.0.message', 'Role not found.');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'primary_role_id' => 999999,
        ]);
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        $playerRoleId = \Spatie\Permission\Models\Role::where('name', Role::PLAYER->value)->first()->id;

        $response = $this->setPrimaryRoleMutation((string) $playerRoleId);

        $response->assertJsonPath('errors.0.message', 'Unauthenticated.');
    }
}
