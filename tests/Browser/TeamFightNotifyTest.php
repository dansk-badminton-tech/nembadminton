<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightNotifyPage;
use Tests\DuskTestCase;

class TeamFightNotifyTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    /**
     * Test sending a notification to manually entered email addresses.
     *
     * Flow: Navigate to the notify page, type a message, select "Holdrunden er opdateret",
     * select "Manuel indtastning", enter email addresses, click send,
     * confirm the dialog, and verify the success snackbar + activity log.
     */
    public function test_user_can_send_notification_to_manual_emails(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();
            $teamRound = TeamRound::where('name', '3x13 Kamps - Valid')->first();

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightNotifyPage($clubhouse->id, $teamRound->id))
                ->on(new TeamFightNotifyPage($clubhouse->id, $teamRound->id))
                ->screenshot('notify-manual-page-loaded');

            // Step 1: Type a message
            $browser->fillMessage('Holdopstillingen er opdateret')
                ->screenshot('notify-manual-message-filled');

            // Step 2: Select "Holdrunden er opdateret"
            $browser->selectTypeUpdated()
                ->screenshot('notify-manual-type-selected');

            // Step 3: Select "Manuel indtastning" and enter emails
            $browser->selectRecipientManualAndFill('player1@example.com, player2@example.com')
                ->screenshot('notify-manual-emails-filled');

            // Step 4: Verify send button text and click
            $browser->assertSeeIn('@send-button', 'Send til alle modtagere')
                ->clickSend()
                ->screenshot('notify-manual-send-clicked');

            // Step 5: Confirm the dialog
            $browser->confirmSendDialog()
                ->screenshot('notify-manual-dialog-confirmed');

            // Step 6: Verify success snackbar
            $browser->waitForText('Beskeden er blevet sendt', 10)
                ->screenshot('notify-manual-email-sent');

            // Step 7: Verify activity log updated
            $browser->waitFor('@activity-feed', 10)
                ->assertSeeIn('@activity-feed', 'Ændringer til holdrunden')
                ->screenshot('notify-manual-activity-log-updated');
        });
    }

    /**
     * Test that the send button is disabled until all required fields are filled.
     */
    public function test_send_button_is_disabled_without_recipient(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();
            $teamRound = TeamRound::where('name', '3x13 Kamps - Valid')->first();

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightNotifyPage($clubhouse->id, $teamRound->id))
                ->on(new TeamFightNotifyPage($clubhouse->id, $teamRound->id));

            // Select manual recipient (no emails entered) → button should be disabled
            $browser->click('@recipient-manual')
                ->waitFor('@manual-emails-input')
                ->assertDisabled('@send-button')
                ->screenshot('notify-button-disabled-no-emails');

            // Fill emails → button should be enabled
            $browser->type('@manual-emails-input', 'player1@example.com')
                ->pause(200)
                ->waitUntilEnabled('@send-button')
                ->screenshot('notify-button-enabled-after-emails');
        });
    }
}
