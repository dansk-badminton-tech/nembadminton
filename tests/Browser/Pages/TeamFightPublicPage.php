<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class TeamFightPublicPage extends Page
{
    private string $teamUUID;

    public function __construct(string $teamUUID = '{teamUUID}')
    {
        $this->teamUUID = $teamUUID;
    }

    public function url(): string
    {
        return '/app/team-fight/' . $this->teamUUID . '/public-view';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitUsing(20, 200, function () use ($browser) {
            $state = $browser->script("
                return document.querySelector(\"[dusk='team-fight-public-page']\") !== null ||
                    document.querySelector(\"[dusk='team-fight-public-not-found']\") !== null;
            ");

            return $state[0] ?? false;
        }, 'Public team fight page did not render any state');
    }

    public function elements(): array
    {
        return [
            '@page' => "[dusk='team-fight-public-page']",
            '@title' => "[dusk='team-fight-public-title']",
            '@game-date' => "[dusk='team-fight-public-game-date']",
            '@squad' => "[dusk='team-fight-public-squad']",
            '@not-found' => "[dusk='team-fight-public-not-found']",
        ];
    }
}
