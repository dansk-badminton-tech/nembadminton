<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class InvitationPage extends Page
{
    public function __construct(private string $token) {}

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return "/app/invitation/{$this->token}";
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@page' => "[dusk='invitation-page']",
            '@sign-up-link' => "[dusk='sign-up-link']",
            '@continue-link' => "[dusk='continue-link']",
            '@accept-button' => "[dusk='accept-button']",
            '@decline-button' => "[dusk='decline-button']",
        ];
    }
}
