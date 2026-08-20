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
                ->on(new TeamFightNotifyPage($clubhouse->id, $teamRound->id));

            // Step 1: Type a message
            $browser->fillMessage('Holdopstillingen er opdateret');

            // Step 2: Select "Holdrunden er opdateret"
            $browser->selectTypeUpdated();

            // Step 3: Select "Manuel indtastning" and enter emails
            $browser->selectRecipientManualAndFill('player1@example.com, player2@example.com');

            // Step 4: Verify send button text and click
            $browser->assertSeeIn('@send-button', 'Send til alle modtagere')
                ->clickSend();

            // Step 5: Confirm the dialog
            $browser->confirmSendDialog();

            // Step 6: Verify success snackbar
            $browser->waitForText('Beskeden er blevet sendt', 10);

            // Step 7: Verify activity log updated
            $browser->waitFor('@activity-feed', 10)
                ->assertSeeIn('@activity-feed', 'Ændringer til holdrunden');
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
                ->assertDisabled('@send-button');

            // Fill emails → button should be enabled
            $browser->type('@manual-emails-input', 'player1@example.com')
                ->pause(200)
                ->waitUntilEnabled('@send-button');
        });
    }

    /**
     * Test that selecting "Alle spillere" lets the user deselect individual players,
     * and that the confirm dialog reflects the reduced count.
     *
     * Flow: Navigate, select platform recipient, wait for player list, read the
     * initial selected count, deselect one player, click send, and assert the
     * confirm dialog shows count minus one.
     */
    public function test_user_can_deselect_players_from_platform_send(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();
            $teamRound = TeamRound::where('name', '3x13 Kamps - Valid')->first();

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightNotifyPage($clubhouse->id, $teamRound->id))
                ->on(new TeamFightNotifyPage($clubhouse->id, $teamRound->id));

            // Select platform recipient — player list appears, all pre-selected
            $browser->selectRecipientPlatform();

            // Read the initial selected count from the list header "(N valgt)"
            $headerText = $browser->text('@player-list .is-size-7');
            preg_match('/\((\d+)\s+valgt\)/', $headerText, $matches);
            $initialCount = (int) $matches[1];

            // Deselect the first player row in the list
            $firstPlayerRefId = $browser->attribute('@player-list .player-row:first-child', 'dusk');
            $firstPlayerRefId = str_replace('player-row-', '', $firstPlayerRefId);
            $browser->togglePlayer($firstPlayerRefId)
                ->pause(300);

            // Click send and verify the confirm dialog shows (initialCount - 1)
            $browser->clickSend();

            $expectedCount = $initialCount - 1;
            $browser->waitForText('Der vil blive sendt', 5)
                ->assertSeeIn('.modal-card-body', "Der vil blive sendt {$expectedCount} e-mail(s)");

            // Cancel — we only verify the dialog text, not the actual send
            $browser->click('.modal-card-foot .button:not(.is-info)');
        });
    }
}
