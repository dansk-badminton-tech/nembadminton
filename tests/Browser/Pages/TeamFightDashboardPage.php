<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class TeamFightDashboardPage extends Page
{
    private int $clubhouseId;

    public function __construct(int $clubhouseId)
    {
        $this->clubhouseId = $clubhouseId;
    }

    public function url(): string
    {
        return '/app/c-' . $this->clubhouseId . '/team-fight/dashboard';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page');
    }

    public function elements(): array
    {
        return [
            '@page' => "[dusk='team-fight-dashboard-page']",
            '@create-team-fight-link' => "[dusk='create-team-fight-link']",
            '@team-fights-table' => ".b-table",
        ];
    }
}
