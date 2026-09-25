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
            '@team-fights-table' => "[dusk='team-fights-table']",
        ];
    }

    public function copyTeamRound(Browser $browser, string $name): void
    {
        $this->clickRowAction($browser, $name, 'Kopier holdrunden');
        $browser->waitFor('.dialog')
            ->click('.dialog .modal-card-foot .button:last-child')
            ->waitForText('Holdrunden kopiret');
    }

    public function deleteTeamRound(Browser $browser, string $name, bool $confirm): void
    {
        $this->clickRowAction($browser, $name, 'Slet holdrunden');
        $browser->waitFor('.dialog')
            ->click($confirm ? '.dialog .modal-card-foot .button:last-child' : '.dialog .modal-card-foot .button:first-child')
            ->waitUntilMissing('.dialog');
    }

    private function clickRowAction(Browser $browser, string $name, string $title): void
    {
        $nameJson = json_encode($name, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $titleJson = json_encode($title, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $browser->script(<<<JS
            const row = Array.from(document.querySelectorAll("[dusk='team-fights-table'] tbody tr"))
                .find(row => row.querySelector('td a')?.textContent.trim() === {$nameJson});
            Array.from(row.querySelectorAll('button')).find(button => button.title === {$titleJson}).click();
        JS);
    }
}
