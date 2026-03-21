<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class CreateCancellationPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/app/cancellations/create';
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
            '@page' => "[dusk='create-cancellation-page']",
            '@no-notification' => "[dusk='no-notification-checkbox']",
            '@email' => "[dusk='email-input']",
            '@clubs' => "[dusk='clubs-taginput'].input",
            '@submit' => "[dusk='submit-button']",
        ];
    }

    /**
     * Create a cancellation collector.
     */
    public function createCollector(Browser $browser, string $club, string $email = null): void
    {
        $browser->type('@clubs', $club)
            ->waitForText($club)
            ->keys('@clubs', ['{enter}']);

        if ($email) {
            $browser->type('@email', $email);
        } else {
            $browser->check('@no-notification');
        }

        $browser->click('@email');

        $browser->waitUntilEnabled('@submit')
            ->click('@submit');
    }
}
