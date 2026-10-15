<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ClubHousePage;
use Tests\Browser\Pages\InvitationPage;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\SignUpPage;
use Tests\DuskTestCase;

class InvitationTest extends DuskTestCase
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
}
