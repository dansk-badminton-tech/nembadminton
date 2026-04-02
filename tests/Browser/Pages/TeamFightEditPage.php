<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class TeamFightEditPage extends Page
{
    private int $clubhouseId;
    private string $teamUUID;

    public function __construct(int $clubhouseId, string $teamUUID)
    {
        $this->clubhouseId = $clubhouseId;
        $this->teamUUID = $teamUUID;
    }

    public function url(): string
    {
        return '/app/c-' . $this->clubhouseId . '/team-fight/' . $this->teamUUID . '/edit';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page');
    }

    public function elements(): array
    {
        return [
            '@page' => "[dusk='team-fight-edit-page']",
            '@player-search-panel' => "[dusk='player-search-panel']",
            '@player-search-input' => "[dusk='player-search-input'] input",
            '@ranking-list-select' => "[dusk='ranking-list-select'] select",
            '@player-search-table' => "[dusk='player-search-table']",
            '@team-table-section' => "[dusk='team-table-section']",
            '@add-teams-section' => "[dusk='add-teams-section']",
            '@add-13-kamps-hold-button' => "[dusk='add-13-kamps-hold-button']",
        ];
    }

    /**
     * Select a ranking list category from the player search dropdown.
     */
    public function selectRankingCategory(Browser $browser, string $category): void
    {
        $browser->select('@ranking-list-select', $category);
    }

    /**
     * Add the first N players from the ranking list by clicking the + button.
     */
    public function addPlayersFromRankingList(Browser $browser, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $browser->waitFor("[dusk='player-search-table'] table tbody tr")
                ->pause(500);

            // Click the first available + (add player) button in the table
            $browser->script("
                var buttons = document.querySelectorAll(\"[dusk='player-search-table'] table tbody tr [dusk='add-player-button']\");
                if (buttons.length > 0) buttons[0].click();
            ");

            // Wait for snackbar confirmation
            $browser->waitForText('Tilføjet til Hold', 10)
                ->pause(500);
        }
    }

    /**
     * Click the 13-kamps hold button to add a squad.
     */
    public function add13KampsHold(Browser $browser): void
    {
        $browser->scrollTo('@add-13-kamps-hold-button')
            ->click('@add-13-kamps-hold-button');
    }
}
