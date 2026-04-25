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
     * Test sending a test email to yourself via the notify page.
     *
     * Flow: Navigate to the notify page for the "Valid" seeded team fight,
     * type a message, select "Holdrunden er klar", select "Test til mig selv",
     * click send, and verify the success snackbar + activity log entry.
     */
    public function test_user_can_send_test_email_to_self(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();
            $teamRound = TeamRound::where('name', '3x13 Kamps - Valid')->first();

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightNotifyPage($clubhouse->id, $teamRound->id))
                ->on(new TeamFightNotifyPage($clubhouse->id, $teamRound->id))
                ->screenshot('notify-page-loaded');

            // Step 1: Type a message
            $browser->fillMessage('Dette er en test besked fra Dusk')
                ->screenshot('notify-message-filled');

            // Step 2: Select notification type (team_publish is default, but click to be explicit)
            $browser->selectTypePublish()
                ->screenshot('notify-type-selected');

            // Step 3: Select "Test til mig selv" as recipient
            $browser->selectRecipientTestSelf()
                ->screenshot('notify-recipient-selected');

            // Step 4: Verify button text changed and click send
            // test_self sends immediately without confirmation dialog
            $browser->assertSeeIn('@send-button', 'Send test email')
                ->clickSend()
                ->screenshot('notify-send-clicked');

            // Step 5: Verify success snackbar
            $browser->waitForText('Test email sendt til', 10)
                ->screenshot('notify-test-email-sent');

            // Step 6: Verify the activity log now shows an entry
            $browser->waitFor('@activity-feed', 10)
                ->assertSeeIn('@activity-feed', 'Test email afsendt')
                ->screenshot('notify-activity-log-updated');
        });
    }

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

            // Send button should be disabled — no recipient selected yet
            $browser->assertDisabled('@send-button')
                ->screenshot('notify-button-disabled-no-recipient');

            // Select test self → button should be enabled
            $browser->selectRecipientTestSelf()
                ->waitUntilEnabled('@send-button')
                ->screenshot('notify-button-enabled-after-recipient');
        });
    }
}
