<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class TeamListPage extends Page
{
    private int $clubhouseId;

    public function __construct(int $clubhouseId)
    {
        $this->clubhouseId = $clubhouseId;
    }

    public function url(): string
    {
        return '/app/c-'.$this->clubhouseId.'/teams';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page')
            ->assertPathIs($this->url());
    }

    /** @return array<string, string> */
    public function elements(): array
    {
        return [
            '@page' => "[dusk='team-list-page']",
            '@create-team-button' => "[dusk='create-team-button']",
        ];
    }
}
