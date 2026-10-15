<?php

namespace Tests\GraphQL;

use App\Enums\Role;
use App\Events\InvitationCreated;
use App\Mail\Invitation as InvitationMail;
use App\Models\Clubhouse;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

/**
 * Managing the Users of a Clubhouse: viewing them, inviting, accepting or
 * declining an invitation, and removing a membership.
 */
class ClubhouseMembershipTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private function userInClubhouse(Clubhouse $clubhouse, ?Role $role = null): User
    {
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        $user->clubhouses()->attach($clubhouse);
        if ($role !== null) {
            setPermissionsTeamId($clubhouse->id);
            $user->assignRole($role->value);
            $user->save();
        }

        return $user->refresh();
    }

    private function userWithoutClubhouse(): User
    {
        return User::factory()->create(['clubhouse_id' => null]);
    }

    private function invitationFor(Clubhouse $clubhouse, User $inviter, Role $role = Role::COACH): Invitation
    {
        return Invitation::query()->create([
            'clubhouse_id' => $clubhouse->id,
            'invited_by' => $inviter->id,
            'invitee_email' => 'invitee@example.com',
            'role' => $role->value,
        ]);
    }

    private function clubhouseQuery(Clubhouse $clubhouse): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            query($id: ID!) {
                clubhouse(id: $id) {
                    id
                    users { id }
                    invitations { id }
                }
            }
        ', ['id' => (string) $clubhouse->id]);
    }

    private function inviteMutation(array $input): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateInvitationInput!) {
                invite(input: $input) { id role status clubhouse { id } inviter { id } }
            }
        ', ['input' => $input]);
    }

    private function acceptMutation(string $token): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($token: ID!) {
                acceptInvitation(token: $token) { id status }
            }
        ', ['token' => $token]);
    }

    private function declineMutation(string $token): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($token: ID!) {
                declineInvitation(token: $token) { id status }
            }
        ', ['token' => $token]);
    }

    private function deleteMembershipMutation(Clubhouse $clubhouse, User $user): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($clubhouseId: ID!, $userId: ID!) {
                deleteMembership(clubhouseId: $clubhouseId, userId: $userId)
            }
        ', ['clubhouseId' => (string) $clubhouse->id, 'userId' => (string) $user->id]);
    }

    // Viewing users and invitations

    /** @test */
    public function a_club_admin_sees_the_users_and_invitations_of_their_clubhouse(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $player = $this->userInClubhouse($clubhouse, Role::PLAYER);
        $invitation = $this->invitationFor($clubhouse, $admin);

        $this->actingAs($admin, 'api');

        $response = $this->clubhouseQuery($clubhouse)->assertJsonMissingPath('errors');
        $this->assertEqualsCanonicalizing(
            [(string) $admin->id, (string) $player->id],
            array_column($response->json('data.clubhouse.users'), 'id')
        );
        $response->assertJsonPath('data.clubhouse.invitations', [['id' => (string) $invitation->id]]);
    }

    /** @test */
    public function a_player_does_not_see_the_users_or_invitations_of_their_clubhouse(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $player = $this->userInClubhouse($clubhouse, Role::PLAYER);
        $this->invitationFor($clubhouse, $admin);

        $this->actingAs($player, 'api');

        $this->clubhouseQuery($clubhouse)
            ->assertJsonPath('data.clubhouse.users', null)
            ->assertJsonPath('data.clubhouse.invitations', null);
    }

    /** @test */
    public function a_club_admin_cannot_see_another_clubhouses_users_or_invitations(): void
    {
        $ownClubhouse = Clubhouse::factory()->create();
        $otherClubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($ownClubhouse, Role::CLUB_ADMIN);
        $otherAdmin = $this->userInClubhouse($otherClubhouse, Role::CLUB_ADMIN);
        $this->invitationFor($otherClubhouse, $otherAdmin);

        $this->actingAs($admin, 'api');

        $this->clubhouseQuery($otherClubhouse)
            ->assertGraphQLErrorMessage('This action is unauthorized.')
            ->assertJsonPath('data.clubhouse', null);
    }

    // Inviting

    /** @test */
    public function a_club_admin_invites_a_user_into_their_own_clubhouse(): void
    {
        Event::fake([InvitationCreated::class]);
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);

        $this->actingAs($admin, 'api');

        $this->inviteMutation(['email' => 'new@example.com', 'role' => 'CLUB_ADMIN'])
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.invite.status', 'PENDING')
            ->assertJsonPath('data.invite.role', Role::CLUB_ADMIN->value)
            ->assertJsonPath('data.invite.clubhouse.id', (string) $clubhouse->id)
            ->assertJsonPath('data.invite.inviter.id', (string) $admin->id);

        $this->assertDatabaseHas('invitations', [
            'invitee_email' => 'new@example.com',
            'clubhouse_id' => $clubhouse->id,
            'invited_by' => $admin->id,
            'status' => 'pending',
        ]);
        Event::assertDispatched(InvitationCreated::class);
    }

    /** @test */
    public function the_invitation_email_is_queued_so_a_mail_failure_does_not_fail_the_invite(): void
    {
        Mail::fake();
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);

        $this->actingAs($admin, 'api');

        $this->inviteMutation(['email' => 'new@example.com', 'role' => 'COACH'])
            ->assertJsonMissingPath('errors');

        Mail::assertQueued(InvitationMail::class, fn (InvitationMail $mail) => $mail->hasTo('new@example.com'));
        Mail::assertNothingSent();
    }

    /** @test */
    public function a_club_admin_cannot_invite_a_user_into_another_clubhouse(): void
    {
        Event::fake([InvitationCreated::class]);
        $ownClubhouse = Clubhouse::factory()->create();
        $otherClubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($ownClubhouse, Role::CLUB_ADMIN);
        $otherAdmin = $this->userInClubhouse($otherClubhouse, Role::CLUB_ADMIN);

        $this->actingAs($admin, 'api');

        $this->inviteMutation([
            'email' => 'intruder@example.com',
            'role' => 'CLUB_ADMIN',
            'inviter' => ['connect' => (string) $otherAdmin->id],
            'clubhouse' => ['connect' => (string) $otherClubhouse->id],
        ]);

        $this->assertDatabaseMissing('invitations', ['clubhouse_id' => $otherClubhouse->id]);
        $this->assertDatabaseMissing('invitations', ['invited_by' => $otherAdmin->id]);
    }

    /** @test */
    public function a_player_cannot_invite(): void
    {
        Event::fake([InvitationCreated::class]);
        $clubhouse = Clubhouse::factory()->create();
        $player = $this->userInClubhouse($clubhouse, Role::PLAYER);

        $this->actingAs($player, 'api');

        $this->inviteMutation(['email' => 'new@example.com', 'role' => 'COACH'])
            ->assertJsonPath('errors.0.message', fn ($message) => $message !== null);

        $this->assertDatabaseCount('invitations', 0);
        Event::assertNotDispatched(InvitationCreated::class);
    }

    /** @test */
    public function a_guest_cannot_invite(): void
    {
        $this->inviteMutation(['email' => 'new@example.com', 'role' => 'COACH'])
            ->assertGraphQLErrorMessage('Unauthenticated.');

        $this->assertDatabaseCount('invitations', 0);
    }

    // Accepting and declining

    /** @test */
    public function accepting_an_invitation_joins_the_clubhouse_with_the_invited_role(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $invitation = $this->invitationFor($clubhouse, $admin, Role::COACH);
        $invitee = $this->userWithoutClubhouse();

        $this->actingAs($invitee, 'api');

        $this->acceptMutation($invitation->token)
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.acceptInvitation.status', 'ACCEPTED');

        $invitee->refresh();
        $this->assertSame($clubhouse->id, $invitee->clubhouse_id);
        $this->assertTrue($invitee->clubhouses->contains($clubhouse));
        setPermissionsTeamId($clubhouse->id);
        $this->assertTrue($invitee->hasRole(Role::COACH->value));
        $this->assertSame(Role::COACH->value, $invitee->primaryRole->name);
        $this->assertNotNull($invitation->refresh()->accepted_at);
    }

    /** @test */
    public function an_expired_invitation_cannot_be_accepted(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $invitation = $this->invitationFor($clubhouse, $admin, Role::CLUB_ADMIN);
        $invitation->expires_at = Carbon::now()->subMinute();
        $invitation->save();
        $invitee = $this->userWithoutClubhouse();

        $this->actingAs($invitee, 'api');

        $this->acceptMutation($invitation->token)
            ->assertGraphQLErrorMessage('Invitationen er udløbet');

        $this->assertSame('pending', $invitation->refresh()->status);
        $this->assertNull($invitee->refresh()->clubhouse_id);
        $this->assertCount(0, $invitee->clubhouses);
    }

    /** @test */
    public function an_expired_invitation_cannot_be_declined(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $invitation = $this->invitationFor($clubhouse, $admin);
        $invitation->expires_at = Carbon::now()->subMinute();
        $invitation->save();

        $this->actingAs($this->userWithoutClubhouse(), 'api');

        $this->declineMutation($invitation->token)
            ->assertGraphQLErrorMessage('Invitationen er udløbet');

        $this->assertSame('pending', $invitation->refresh()->status);
    }

    /** @test */
    public function the_invitation_link_tells_whether_it_has_expired(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $valid = $this->invitationFor($clubhouse, $admin);
        $expired = $this->invitationFor($clubhouse, $admin);
        $expired->expires_at = Carbon::now()->subMinute();
        $expired->save();

        $query = /** @lang GraphQL */ 'query($token: ID!) { invitation(token: $token) { expired } }';

        $this->graphQL($query, ['token' => $valid->token])->assertJsonPath('data.invitation.expired', false);
        $this->graphQL($query, ['token' => $expired->token])->assertJsonPath('data.invitation.expired', true);
    }

    /** @test */
    public function an_accepted_invitation_cannot_be_accepted_again(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $invitation = $this->invitationFor($clubhouse, $admin);
        $invitation->accept();
        $invitation->save();

        $this->actingAs($this->userWithoutClubhouse(), 'api');

        $this->acceptMutation($invitation->token)
            ->assertJsonPath('errors.0.message', fn ($message) => $message !== null)
            ->assertJsonPath('data.acceptInvitation', null);
    }

    /** @test */
    public function declining_an_invitation_does_not_join_the_clubhouse(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $invitation = $this->invitationFor($clubhouse, $admin);
        $invitee = $this->userWithoutClubhouse();

        $this->actingAs($invitee, 'api');

        $this->declineMutation($invitation->token)
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.declineInvitation.status', 'DECLINED');

        $this->assertNull($invitee->refresh()->clubhouse_id);
    }

    // Removing a membership

    /** @test */
    public function a_club_admin_removes_a_member_from_their_clubhouse(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $player = $this->userInClubhouse($clubhouse, Role::PLAYER);

        $this->actingAs($admin, 'api');

        $this->deleteMembershipMutation($clubhouse, $player)
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.deleteMembership', true);

        $player->refresh();
        $this->assertNull($player->clubhouse_id);
        $this->assertFalse($player->clubhouses->contains($clubhouse));
        setPermissionsTeamId($clubhouse->id);
        $this->assertFalse($player->hasRole(Role::PLAYER->value));
    }

    /** @test */
    public function removing_a_member_keeps_their_membership_of_other_clubhouses(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $otherClubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $member = $this->userInClubhouse($clubhouse, Role::COACH);
        $member->clubhouses()->attach($otherClubhouse);
        setPermissionsTeamId($otherClubhouse->id);
        $member->assignRole(Role::PLAYER->value);

        $this->actingAs($admin, 'api');

        $this->deleteMembershipMutation($clubhouse, $member)->assertJsonMissingPath('errors');

        $member->refresh();
        $this->assertFalse($member->clubhouses->contains($clubhouse));
        $this->assertTrue($member->clubhouses->contains($otherClubhouse));
        setPermissionsTeamId($otherClubhouse->id);
        $this->assertTrue($member->unsetRelation('roles')->hasRole(Role::PLAYER->value));
    }

    /** @test */
    public function a_club_admin_cannot_remove_themselves(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);

        $this->actingAs($admin, 'api');

        $this->deleteMembershipMutation($clubhouse, $admin)
            ->assertGraphQLErrorMessage('You cannot delete yourself');

        $this->assertSame($clubhouse->id, $admin->refresh()->clubhouse_id);
    }

    /** @test */
    public function a_club_admin_cannot_remove_a_member_of_another_clubhouse(): void
    {
        $ownClubhouse = Clubhouse::factory()->create();
        $otherClubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($ownClubhouse, Role::CLUB_ADMIN);
        $otherPlayer = $this->userInClubhouse($otherClubhouse, Role::PLAYER);

        $this->actingAs($admin, 'api');

        $this->deleteMembershipMutation($otherClubhouse, $otherPlayer)
            ->assertGraphQLErrorMessage('You cannot delete someone elses membership');
        $this->deleteMembershipMutation($ownClubhouse, $otherPlayer)
            ->assertGraphQLErrorMessage('You cannot delete someone elses membership');

        $this->assertSame($otherClubhouse->id, $otherPlayer->refresh()->clubhouse_id);
    }

    /** @test */
    public function a_player_cannot_remove_a_member(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $player = $this->userInClubhouse($clubhouse, Role::PLAYER);
        $otherPlayer = $this->userInClubhouse($clubhouse, Role::PLAYER);

        $this->actingAs($player, 'api');

        $this->deleteMembershipMutation($clubhouse, $otherPlayer)
            ->assertJsonPath('errors.0.message', fn ($message) => $message !== null);

        $this->assertSame($clubhouse->id, $otherPlayer->refresh()->clubhouse_id);
    }
}
