<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class ClubHousePage extends Page
{
    public function __construct(private int $clubhouseId) {}

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return "/app/c-{$this->clubhouseId}/club-house";
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForText('Invitationer');
    }

    /**
     * Switch to the Invitationer tab.
     */
    public function openInvitations(Browser $browser): void
    {
        $browser->clickLink('Invitationer')
            ->waitFor('@invitation-list');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@invitation-list' => "[dusk='invitation-list']",
        ];
    }

    /**
     * Delete an invitation from the list and confirm the dialog.
     */
    public function deleteInvitation(Browser $browser, int $invitationId): void
    {
        $browser->waitFor("[dusk='delete-invitation-{$invitationId}']")
            ->click("[dusk='delete-invitation-{$invitationId}']")
            ->waitFor('.dialog')
            ->click('.dialog .modal-card-foot .button:last-child');
    }
}
