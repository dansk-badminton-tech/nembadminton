<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class CancellationLandingPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/app/cancellations/landing';
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
            '@page' => "[dusk='cancellation-landing-page']",
            '@get-started' => "[dusk='get-started-button']",
        ];
    }
}
