<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ClubHousePage;
use Tests\Browser\Pages\InvitationPage;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\SignUpPage;
use Tests\DuskTestCase;

class ClubhouseMembershipTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private function pendingInvitation(string $role = 'club-admin'): Invitation
    {
        return Invitation::query()->create([
            'clubhouse_id' => Clubhouse::first()->id,
            'invited_by' => User::where('email', 'testing@gmail.com')->firstOrFail()->id,
            'invitee_email' => 'new-admin@example.com',
            'role' => $role,
        ]);
    }

    public function test_guest_opening_an_invitation_link_must_sign_up_before_accepting(): void
    {
        $invitation = $this->pendingInvitation();

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit(new InvitationPage($invitation->token))
                ->waitForText('Invitation til: '.$invitation->clubhouse->name)
                ->assertSee('Du skal oprette dig som bruger først')
                ->assertVisible('@sign-up-link')
                ->assertMissing('@accept-button')
                ->assertMissing('@decline-button')
                ->click('@sign-up-link')
                ->on(new SignUpPage)
                ->assertQueryStringHas('invitationToken', $invitation->token);
        });
    }

    public function test_logged_in_user_can_accept_or_decline_an_invitation(): void
    {
        $invitation = $this->pendingInvitation('coach');

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new InvitationPage($invitation->token))
                ->waitFor('@accept-button')
                ->assertVisible('@decline-button')
                ->assertMissing('@sign-up-link')
                ->assertDontSee('Du skal oprette dig som bruger først');
        });
    }

    public function test_club_admin_can_delete_a_pending_invitation(): void
    {
        $invitation = $this->pendingInvitation();

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new ClubHousePage($invitation->clubhouse_id))
                ->openInvitations()
                ->waitForTextIn('@invitation-list', 'new-admin@example.com')
                ->deleteInvitation($invitation->id)
                ->waitForText('Invitation slettet')
                ->waitUntilMissingText('new-admin@example.com');
        });

        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }

    public function test_expired_invitation_link_cannot_be_accepted(): void
    {
        $invitation = $this->pendingInvitation('coach');
        $invitation->expires_at = Carbon::now()->subDay();
        $invitation->save();

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new InvitationPage($invitation->token))
                ->waitFor('@expired')
                ->assertSeeIn('@expired', 'Invitationen er udløbet')
                ->assertMissing('@accept-button')
                ->assertMissing('@decline-button');
        });
    }

    public function test_expired_invitation_link_does_not_ask_a_guest_to_sign_up(): void
    {
        $invitation = $this->pendingInvitation();
        $invitation->expires_at = Carbon::now()->subDay();
        $invitation->save();

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit(new InvitationPage($invitation->token))
                ->waitFor('@expired')
                ->assertMissing('@sign-up-link')
                ->assertMissing('@accept-button');
        });
    }

    public function test_club_admin_invites_a_member_into_their_clubhouse(): void
    {
        $clubhouse = Clubhouse::first();

        $this->browse(function (Browser $browser) use ($clubhouse) {
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new ClubHousePage($clubhouse->id))
                ->openInvitations()
                ->inviteMember('coach@example.com')
                ->waitForText('Invitationen er blevet sendt')
                ->waitForTextIn('@invitation-list', 'coach@example.com');
        });

        $this->assertDatabaseHas('invitations', [
            'invitee_email' => 'coach@example.com',
            'clubhouse_id' => $clubhouse->id,
            'invited_by' => User::where('email', 'testing@gmail.com')->firstOrFail()->id,
            'status' => 'pending',
        ]);
    }

    public function test_club_admin_removes_a_member_from_their_clubhouse(): void
    {
        $clubhouse = Clubhouse::first();
        $member = User::factory()->create(['clubhouse_id' => $clubhouse->id, 'email' => 'leaving@example.com']);
        $member->clubhouses()->attach($clubhouse);

        $this->browse(function (Browser $browser) use ($clubhouse, $member) {
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new ClubHousePage($clubhouse->id))
                ->waitForTextIn('@member-list', 'leaving@example.com')
                ->removeMember($member->id)
                ->waitForText('Medlem slettet')
                ->waitUntilMissingText('leaving@example.com');
        });

        $this->assertNull($member->refresh()->clubhouse_id);
    }
}
