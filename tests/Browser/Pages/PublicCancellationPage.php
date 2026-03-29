<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class PublicCancellationPage extends Page
{
    private string $sharingId;

    public function __construct(string $sharingId = '{sharingId}')
    {
        $this->sharingId = $sharingId;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/app/cancellation/' . $this->sharingId . '/public-cancellation';
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
            '@page' => "[dusk='public-cancellation-page']",
            '@player-search' => "[dusk='player-search'] .input",
            '@player-search-results-first' => "[dusk='player-search'] .dropdown-menu .dropdown-item.is-hovered",
            '@email' => "[dusk='email-input']",
            '@datepicker' => "[dusk='datepicker-input']",
            '@message' => "[dusk='message-input']",
            '@submit' => "[dusk='submit-button']",
        ];
    }

    /**
     * Fill and submit the public cancellation form.
     */
    public function submitCancellation(Browser $browser, string $playerName, string $email, string $message = ''): void
    {
        $browser->waitUntilEnabled('@player-search')
            ->type('@player-search', substr($playerName, 0, -1))
            ->waitForText($playerName)
            ->waitFor('@player-search-results-first')
            ->keys('@player-search', '{enter}')
            ->type('@email', $email)
            ->click('@datepicker')
            ->waitFor('.datepicker')
            ->click('.datepicker .datepicker-table .datepicker-cell.is-today')
            ->click('@page') // Close datepicker
            ->type('@message', $message)
            ->click('@submit')
            ->waitForText('Send afbud')
            ->click('.modal-card-foot .button.is-success');
    }
}
