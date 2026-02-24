<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class PublicCancellationFinishPage extends Page
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
        return '/app/cancellation/' . $this->sharingId . '/public-cancellation/finish';
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
            '@page' => "[dusk='public-cancellation-finish-page']",
            '@new-cancellation' => "[dusk='new-cancellation-button']",
        ];
    }
}
