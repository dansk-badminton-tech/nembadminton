<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class TeamFightCreatePage extends Page
{
    private int $clubhouseId;

    public function __construct(int $clubhouseId)
    {
        $this->clubhouseId = $clubhouseId;
    }

    public function url(): string
    {
        return '/app/c-' . $this->clubhouseId . '/team-fight/create';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page');
    }

    public function elements(): array
    {
        return [
            '@page' => "[dusk='team-fight-create-page']",
            '@name-input' => "[dusk='team-fight-name-input']",
            '@date-picker' => "[dusk='team-fight-date-picker']",
            '@ranking-select' => "[dusk='team-fight-ranking-select'] select",
            '@submit-button' => "[dusk='team-fight-submit-button']",
        ];
    }

    /**
     * Select a day from the open datepicker by day number (1-31).
     */
    public function selectDay(Browser $browser, int $day): void
    {
        $browser->click('@date-picker')
            ->waitFor('.datepicker .dropdown-content')
            ->script("document.querySelectorAll('.datepicker .datepicker-body a.datepicker-cell.is-selectable')[" . ($day - 1) . "].click()");
    }

    /**
     * Fill in and submit the create team fight form.
     */
    public function createTeamFight(Browser $browser, string $name, int $day, string $rankingVersion): void
    {
        $browser->waitUntilEnabled('@name-input')
            ->type('@name-input', $name);

        $this->selectDay($browser, $day);

        $browser->select('@ranking-select', $rankingVersion)
            ->click('@submit-button');
    }
}
