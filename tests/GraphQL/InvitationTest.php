<?php

namespace Tests\GraphQL;

use App\Enums\Role;
use App\Models\Clubhouse;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private function userInClubhouse(Clubhouse $clubhouse, Role $role): User
    {
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->assignRole($role->value);
        $user->save();

        return $user->refresh();
    }

    private function invitationFor(Clubhouse $clubhouse, User $inviter, string $role = 'coach'): Invitation
    {
        return Invitation::query()->create([
            'clubhouse_id' => $clubhouse->id,
            'invited_by' => $inviter->id,
            'invitee_email' => 'invitee@example.com',
            'role' => $role,
        ]);
    }

    private function deleteInvitationMutation(string $id): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) {
                deleteInvitation(id: $id) { id status }
            }
        ', ['id' => $id]);
    }

    /** @test */
    public function a_club_admin_can_delete_a_pending_invitation_in_their_clubhouse(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $invitation = $this->invitationFor($clubhouse, $admin);

        $this->actingAs($admin, 'api');

        $this->deleteInvitationMutation((string) $invitation->id)
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.deleteInvitation.id', (string) $invitation->id);

        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }

    /** @test */
    public function a_club_admin_cannot_delete_another_clubhouses_invitation(): void
    {
        $ownClubhouse = Clubhouse::factory()->create();
        $otherClubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($ownClubhouse, Role::CLUB_ADMIN);
        $otherAdmin = $this->userInClubhouse($otherClubhouse, Role::CLUB_ADMIN);
        $invitation = $this->invitationFor($otherClubhouse, $otherAdmin);

        $this->actingAs($admin, 'api');

        $this->deleteInvitationMutation((string) $invitation->id)
            ->assertGraphQLErrorMessage('This action is unauthorized.');

        $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
    }

    /** @test */
    public function a_coach_cannot_delete_an_invitation(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $coach = $this->userInClubhouse($clubhouse, Role::COACH);
        $invitation = $this->invitationFor($clubhouse, $admin);

        $this->actingAs($coach, 'api');

        $this->deleteInvitationMutation((string) $invitation->id)
            ->assertJsonPath('errors.0.message', fn ($message) => $message !== null);

        $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
    }

    /** @test */
    public function a_guest_cannot_accept_an_invitation(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $admin = $this->userInClubhouse($clubhouse, Role::CLUB_ADMIN);
        $invitation = $this->invitationFor($clubhouse, $admin, Role::CLUB_ADMIN->value);

        $this->graphQL(/** @lang GraphQL */ '
            mutation($token: ID!) {
                acceptInvitation(token: $token) { id status }
            }
        ', ['token' => $invitation->token])
            ->assertGraphQLErrorMessage('Unauthenticated.');

        $this->assertSame('pending', $invitation->refresh()->status);
    }
}
