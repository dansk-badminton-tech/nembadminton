<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class TeamFightNotifyPage extends Page
{
    private int $clubhouseId;
    private string $teamUUID;

    public function __construct(int $clubhouseId, string $teamUUID)
    {
        $this->clubhouseId = $clubhouseId;
        $this->teamUUID = $teamUUID;
    }

    public function url(): string
    {
        return '/app/c-' . $this->clubhouseId . '/team-fight/' . $this->teamUUID . '/notify';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page');
    }

    public function elements(): array
    {
        return [
            '@page'                    => "[dusk='notify-page']",
            '@back-button'             => "[dusk='notify-back-button']",
            '@message-input'           => "textarea[dusk='notify-message-input']",
            '@type-publish'            => "[dusk='notify-type-publish']",
            '@type-updated'            => "[dusk='notify-type-updated']",
            '@recipient-manual'        => "[dusk='notify-recipient-manual']",
            '@recipient-platform'      => "[dusk='notify-recipient-platform']",
            '@player-list'              => "[dusk='notify-player-list']",
            '@select-all-players'       => "[dusk='select-all-players']",
            '@deselect-all-players'     => "[dusk='deselect-all-players']",
            '@manual-emails-input'     => "textarea[dusk='notify-manual-emails-input']",
            '@save-emails-checkbox'    => "[dusk='notify-save-emails-checkbox'] input",
            '@send-button'             => "[dusk='notify-send-button']",
            '@empty-activity'          => "[dusk='notify-empty-activity']",
            '@activity-feed'           => "[dusk='notify-activity-feed']",
        ];
    }

    /**
     * Select "Holdrunden er klar" as the notification type.
     */
    public function selectTypePublish(Browser $browser): void
    {
        $browser->click('@type-publish')
            ->pause(200);
    }

    /**
     * Select "Holdrunden er opdateret" as the notification type.
     */
    public function selectTypeUpdated(Browser $browser): void
    {
        $browser->click('@type-updated')
            ->pause(200);
    }

    /**
     * Select "Manuel indtastning" as the recipient method and enter emails.
     */
    public function selectRecipientManualAndFill(Browser $browser, string $emails): void
    {
        $browser->click('@recipient-manual')
            ->waitFor('@manual-emails-input')
            ->clear('@manual-emails-input')
            ->type('@manual-emails-input', $emails)
            ->pause(200);
    }

    /**
     * Select "Alle spillere på holdrunden" as the recipient method and wait for the player list.
     */
    public function selectRecipientPlatform(Browser $browser): void
    {
        $browser->click('@recipient-platform')
            ->waitFor('@player-list')
            ->pause(200);
    }

    /**
     * Toggle a single player row by refId.
     */
    public function togglePlayer(Browser $browser, string $refId): void
    {
        $browser->click("@player-row-{$refId}")
            ->pause(100);
    }

    /**
     * Toggle an entire squad by squad id.
     */
    public function toggleSquad(Browser $browser, string $squadId): void
    {
        $browser->click("@squad-toggle-{$squadId}")
            ->pause(100);
    }

    /**
     * Click the "Vælg alle" shortcut.
     */
    public function selectAllPlayers(Browser $browser): void
    {
        $browser->click('@select-all-players')
            ->pause(100);
    }

    /**
     * Click the "Fravælg alle" shortcut.
     */
    public function deselectAllPlayers(Browser $browser): void
    {
        $browser->click('@deselect-all-players')
            ->pause(100);
    }

    /**
     * Type a message into the message textarea.
     */
    public function fillMessage(Browser $browser, string $message): void
    {
        $browser->clear('@message-input')
            ->type('@message-input', $message);
    }

    /**
     * Click the send button. Assumes it is enabled.
     */
    public function clickSend(Browser $browser): void
    {
        $browser->waitUntilEnabled('@send-button')
            ->click('@send-button');
    }

    /**
     * Confirm the Buefy dialog that appears before sending.
     */
    public function confirmSendDialog(Browser $browser): void
    {
        $browser->waitFor('.modal-card-foot .button.is-info')
            ->click('.modal-card-foot .button.is-info');
    }
}
