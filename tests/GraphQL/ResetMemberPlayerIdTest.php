<?php

namespace Tests\GraphQL;

use App\Enums\Role;
use App\Models\Clubhouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class ResetMemberPlayerIdTest extends TestCase
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

    private function memberInClubhouse(Clubhouse $clubhouse, array $roleValues = [], ?string $playerId = null): User
    {
        $member = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'player_id' => $playerId,
        ]);
        setPermissionsTeamId($clubhouse->id);
        foreach ($roleValues as $r) {
            $member->assignRole($r);
        }
        $member->save();
        return $member->refresh();
    }

    private function resetMutation(string $clubhouseId, string $userId): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($clubhouseId: ID!, $userId: ID!) {
                resetMemberPlayerId(clubhouseId: $clubhouseId, userId: $userId) {
                    id
                    player_id
                }
            }
        ', [
            'clubhouseId' => $clubhouseId,
            'userId' => $userId,
        ]);
    }

    /** @test */
    public function it_lets_a_club_admin_reset_a_coachs_player_id(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->clubAdminInClubhouse($clubhouse);
        $coach = $this->memberInClubhouse($clubhouse, [Role::COACH->value], '010203-01');

        $this->actingAs($admin, 'api');

        $response = $this->resetMutation((string) $clubhouse->id, (string) $coach->id);

        $response->assertJson([
            'data' => [
                'resetMemberPlayerId' => [
                    'id' => (string) $coach->id,
                    'player_id' => null,
                ],
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $coach->id,
            'player_id' => null,
        ]);
    }

    /** @test */
    public function it_rejects_resetting_a_players_player_id(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->clubAdminInClubhouse($clubhouse);
        $player = $this->memberInClubhouse($clubhouse, [Role::PLAYER->value], '010203-01');

        $this->actingAs($admin, 'api');

        $response = $this->resetMutation((string) $clubhouse->id, (string) $player->id);

        $response->assertJsonPath('errors.0.message', 'Cannot reset player_id for a player');
        $this->assertDatabaseHas('users', [
            'id' => $player->id,
            'player_id' => '010203-01',
        ]);
    }

    /** @test */
    public function it_rejects_non_club_admin(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $coach = $this->memberInClubhouse($clubhouse, [Role::COACH->value], '010203-01');
        $target = $this->memberInClubhouse($clubhouse, [Role::CLUB_ADMIN->value], '040506-02');

        $this->actingAs($coach, 'api');

        $response = $this->resetMutation((string) $clubhouse->id, (string) $target->id);

        $response->assertJsonPath('errors.0.message', 'Only club admins can reset player_id');
    }

    /** @test */
    public function it_rejects_self_reset(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->clubAdminInClubhouse($clubhouse);
        $admin->player_id = '010203-01';
        $admin->save();

        $this->actingAs($admin, 'api');

        $response = $this->resetMutation((string) $clubhouse->id, (string) $admin->id);

        $response->assertJsonPath('errors.0.message', 'You cannot reset your own player_id');
    }

    /** @test */
    public function it_rejects_cross_clubhouse_reset(): void
    {
        $clubhouseA = Clubhouse::factory()->create();
        $clubhouseB = Clubhouse::factory()->create();
        $adminA = $this->clubAdminInClubhouse($clubhouseA);
        $memberB = $this->memberInClubhouse($clubhouseB, [Role::COACH->value], '010203-01');

        $this->actingAs($adminA, 'api');

        $response = $this->resetMutation((string) $clubhouseA->id, (string) $memberB->id);

        $response->assertJsonPath('errors.0.message', 'You cannot edit someone elses membership');
    }
}
