<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class SignUpFinishPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/app/sign-up/finish';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitFor('@form');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@form' => "[dusk='sign-up-finish-form']",
            '@name-input' => "[dusk='clubhouse-name-input']",
            '@email-input' => "[dusk='clubhouse-email-input']",
            '@club-select' => "[dusk='club-select']",
            '@submit-button' => "[dusk='create-clubhouse-button']",
        ];
    }

    /**
     * Create a new clubhouse.
     */
    public function createClubhouse(Browser $browser, string $name, string $email, int $clubId): void
    {
        $browser->type('@name-input', $name)
            ->type('@email-input', $email)
            ->select('@club-select', $clubId)
            ->pressAndWaitFor('@submit-button');
    }
}
