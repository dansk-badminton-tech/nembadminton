<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class ClubDashboardPage extends Page
{
    private $clubhouseId;

    public function __construct($clubhouseId = null)
    {
        $this->clubhouseId = $clubhouseId;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        if ($this->clubhouseId) {
            return "/app/c-{$this->clubhouseId}/home";
        }
        return '/app/home';
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
            '@page' => "[dusk='club-dashboard-page']",
        ];
    }
}
