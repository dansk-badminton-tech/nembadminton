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
            '@member-list' => "[dusk='member-list']",
            '@invitation-email-input' => "input[dusk='invitation-email-input']",
            '@send-invitation-button' => "[dusk='send-invitation-button']",
        ];
    }

    /**
     * Invite someone from the Invitationer tab, keeping the default role.
     */
    public function inviteMember(Browser $browser, string $email): void
    {
        $browser->waitFor("[dusk='open-invite-modal']")
            ->click("[dusk='open-invite-modal']")
            ->waitFor('@invitation-email-input')
            ->type('@invitation-email-input', $email)
            ->click('@send-invitation-button');
    }

    /**
     * Remove a User from the Medlemmer tab.
     */
    public function removeMember(Browser $browser, int $userId): void
    {
        $browser->waitFor("[dusk='remove-member-{$userId}']")
            ->click("[dusk='remove-member-{$userId}']");
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
