<?php

namespace Tests\GraphQL;

use App\Enums\Role;
use App\Models\Club;
use App\Models\Clubhouse;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// [DEBUG-pr01] throwaway probe: every path must keep "clubhouse_id set => primary_role_id set"
class PrimaryRoleProbeTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private function userInClubhouse(Clubhouse $clubhouse, Role $role): User
    {
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        $user->clubhouses()->attach($clubhouse);
        setPermissionsTeamId($clubhouse->id);
        $user->assignRole($role->value);
        $user->primaryRole()->associate($user->roles->first());
        $user->save();

        return $user->refresh();
    }

    private function assertInvariant(User $user): void
    {
        $user->refresh();
        $this->assertNotNull($user->clubhouse_id, 'precondition: user has clubhouse');
        $this->assertNotNull($user->primary_role_id, "user {$user->id} has clubhouse_id={$user->clubhouse_id} and primary_role_id=null");
    }

    // Recovery probes: start stuck (clubhouse_id set, no roles, primary_role_id null), recover through UI mutations only

    private function stuckUserIn(Clubhouse $clubhouse): User
    {
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id, 'primary_role_id' => null]);
        $user->clubhouses()->attach($clubhouse);

        return $user->refresh();
    }

    private function invite(Clubhouse $clubhouse, User $admin, string $role = 'player'): Invitation
    {
        return Invitation::query()->create(['clubhouse_id' => $clubhouse->id, 'invited_by' => $admin->id, 'invitee_email' => 'i@example.com', 'role' => $role]);
    }

    #[Test]
    public function recover_admin_gives_role_again(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $stuck = $this->stuckUserIn($clubhouse);
        $this->actingAs($admin, 'api');

        $this->graphQL('mutation($c: ID!, $u: ID!) { updateMembershipRoles(clubhouseId: $c, userId: $u, roles: [PLAYER]) { id } }',
            ['c' => (string) $clubhouse->id, 'u' => (string) $stuck->id])->assertJsonMissingPath('errors');

        $this->assertInvariant($stuck);
    }

    #[Test]
    public function recover_admin_sees_stuck_user_in_member_list(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $stuck = $this->stuckUserIn($clubhouse);
        $this->actingAs($admin, 'api');

        $ids = $this->graphQL('query($id: ID!) { clubhouse(id: $id) { users { id } } }', ['id' => (string) $clubhouse->id])
            ->assertJsonMissingPath('errors')->json('data.clubhouse.users.*.id');

        $this->assertContains((string) $stuck->id, $ids);
    }

    #[Test]
    public function recover_accept_invitation_to_same_clubhouse(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $stuck = $this->stuckUserIn($clubhouse);
        $invitation = $this->invite($clubhouse, $admin);
        $this->actingAs($stuck, 'api');

        $this->graphQL('mutation($t: ID!) { acceptInvitation(token: $t) { id } }', ['t' => $invitation->token])->assertJsonMissingPath('errors');

        $this->assertInvariant($stuck);
    }

    #[Test]
    public function recover_accept_invitation_to_other_clubhouse(): void
    {
        $a = Clubhouse::factory()->create();
        $b = Clubhouse::factory()->create();
        $adminB = $this->userInClubhouse($b, Role::CLUB_ADMIN);
        $stuck = $this->stuckUserIn($a);
        $invitation = $this->invite($b, $adminB);
        $this->actingAs($stuck, 'api');

        $this->graphQL('mutation($t: ID!) { acceptInvitation(token: $t) { id } }', ['t' => $invitation->token])->assertJsonMissingPath('errors');

        $this->assertInvariant($stuck);
        $this->assertSame($b->id, $stuck->clubhouse_id);
    }

    #[Test]
    public function recover_user_sets_own_primary_role(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $stuck = $this->stuckUserIn($clubhouse);
        $this->actingAs($stuck, 'api');
        $playerRoleId = \Spatie\Permission\Models\Role::where('name', Role::PLAYER->value)->first()->id;

        $this->graphQL('mutation($r: ID!) { setPrimaryRole(roleId: $r) { id } }', ['r' => (string) $playerRoleId])->assertJsonMissingPath('errors');

        $this->assertInvariant($stuck);
    }

    #[Test]
    public function recover_admin_removes_membership(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $stuck = $this->stuckUserIn($clubhouse);
        $this->actingAs($admin, 'api');

        $this->graphQL('mutation($c: ID!, $u: ID!) { deleteMembership(clubhouseId: $c, userId: $u) }',
            ['c' => (string) $clubhouse->id, 'u' => (string) $stuck->id])->assertJsonMissingPath('errors');

        $this->assertNull($stuck->refresh()->clubhouse_id, 'no longer stuck: no clubhouse, so sign-up can create one');
    }

    #[Test]
    public function update_roles_to_empty(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $target = $this->userInClubhouse($clubhouse, Role::PLAYER);
        $this->actingAs($admin, 'api');

        $this->graphQL('mutation($c: ID!, $u: ID!) { updateMembershipRoles(clubhouseId: $c, userId: $u, roles: []) { id } }',
            ['c' => (string) $clubhouse->id, 'u' => (string) $target->id])->assertJsonMissingPath('errors');

        $this->assertInvariant($target);
    }

    #[Test]
    public function create_clubhouse(): void
    {
        Bus::fake();
        $club = Club::query()->forceCreate(['id' => 9001, 'name1' => 'K', 'badmintonPlayerId' => 9001, 'initialized' => true]);
        $user = User::factory()->create(['primary_role_id' => null]);
        $this->actingAs($user, 'api');

        $this->graphQL('mutation($input: CreateClubhouseInput!) { createClubhouse(input: $input) { id } }', ['input' => [
            'name' => 'X', 'email' => 'x@example.com',
            'user' => ['connect' => [(string) $user->id]],
            'users' => ['connect' => [(string) $user->id]],
            'clubs' => ['connect' => [(string) $club->id]],
        ]])->assertJsonMissingPath('errors');

        $this->assertInvariant($user);
    }

    #[Test]
    public function create_clubhouse_connecting_another_user(): void
    {
        Bus::fake();
        $club = Club::query()->forceCreate(['id' => 9002, 'name1' => 'K', 'badmintonPlayerId' => 9002, 'initialized' => true]);
        $actor = User::factory()->create(['primary_role_id' => null]);
        $other = User::factory()->create(['primary_role_id' => null]);
        $this->actingAs($actor, 'api');

        $this->graphQL('mutation($input: CreateClubhouseInput!) { createClubhouse(input: $input) { id } }', ['input' => [
            'name' => 'X', 'email' => 'x@example.com',
            'user' => ['connect' => [(string) $actor->id, (string) $other->id]],
            'users' => ['connect' => [(string) $actor->id]],
            'clubs' => ['connect' => [(string) $club->id]],
        ]])->assertJsonMissingPath('errors');

        $this->assertInvariant($other);
    }

    #[Test]
    public function accept_invitation_without_clubhouse(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $invitation = Invitation::query()->create(['clubhouse_id' => $clubhouse->id, 'invited_by' => $admin->id, 'invitee_email' => 'i@example.com', 'role' => 'player']);
        $invitee = User::factory()->create(['primary_role_id' => null]);
        $this->actingAs($invitee, 'api');

        $this->graphQL('mutation($t: ID!) { acceptInvitation(token: $t) { id } }', ['t' => $invitation->token])->assertJsonMissingPath('errors');

        $this->assertInvariant($invitee);
    }

    #[Test]
    public function accept_invitation_while_in_other_clubhouse(): void
    {
        $a = Clubhouse::factory()->create();
        $b = Clubhouse::factory()->create();
        $adminB = $this->userInClubhouse($b, Role::CLUB_ADMIN);
        $invitee = $this->userInClubhouse($a, Role::COACH);
        $invitation = Invitation::query()->create(['clubhouse_id' => $b->id, 'invited_by' => $adminB->id, 'invitee_email' => 'i@example.com', 'role' => 'player']);
        $this->actingAs($invitee, 'api');

        $this->graphQL('mutation($t: ID!) { acceptInvitation(token: $t) { id } }', ['t' => $invitation->token])->assertJsonMissingPath('errors');

        $this->assertInvariant($invitee);
    }

    #[Test]
    public function delete_membership_with_remaining_clubhouse(): void
    {
        $a = Clubhouse::factory()->create();
        $b = Clubhouse::factory()->create();
        $adminA = $this->userInClubhouse($a, Role::CLUB_ADMIN);
        $target = $this->userInClubhouse($b, Role::PLAYER);
        // target then joins A as player and A becomes current
        $target->clubhouses()->attach($a);
        setPermissionsTeamId($a->id);
        $target->assignRole(Role::PLAYER->value);
        $target->clubhouse()->associate($a)->save();
        $this->actingAs($adminA, 'api');

        $this->graphQL('mutation($c: ID!, $u: ID!) { deleteMembership(clubhouseId: $c, userId: $u) }',
            ['c' => (string) $a->id, 'u' => (string) $target->id])->assertJsonMissingPath('errors');

        $this->assertInvariant($target);
    }
}
