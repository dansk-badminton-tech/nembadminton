<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class UpdateCancellationPage extends Page
{
    private string $collectorId;

    public function __construct(string $collectorId = '{collectorId}')
    {
        $this->collectorId = $collectorId;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/app/cancellations/edit/' . $this->collectorId;
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
            '@page' => "[dusk='update-cancellation-page']",
            '@no-notification' => "[dusk='no-notification-checkbox']",
            '@email' => "[dusk='email-input']",
            '@clubs' => "[dusk='clubs-taginput'] input",
            '@submit' => "[dusk='submit-button']",
        ];
    }

    /**
     * Update the cancellation collector.
     */
    public function updateCollector(Browser $browser, string $email = null): void
    {
        if ($email) {
            $browser->uncheck('@no-notification')
                ->type('@email', $email);
        } else {
            $browser->check('@no-notification');
        }

        $browser->click('@submit');
    }
}
