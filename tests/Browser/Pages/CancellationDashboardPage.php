<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class CancellationDashboardPage extends Page
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
        return '/app/cancellations/view/' . $this->collectorId;
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
            '@page' => "[dusk='cancellation-dashboard-page']",
            '@edit' => "[dusk='edit-button']",
            '@delete' => "[dusk='delete-button']",
            '@table' => "[dusk='cancellations-table']",
        ];
    }
}
