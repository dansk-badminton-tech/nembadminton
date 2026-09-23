<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\HelpGuidePage;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamFightMissingPlayerGuideTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_empty_player_search_links_to_the_missing_player_guide(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234');

            $createPage = new TeamFightCreatePage($clubhouse->id);
            $browser->visit($createPage)
                ->on($createPage);

            $browser->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Manglende Spiller Holdrunde');

            $browser->on($createPage)
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025');

            $browser->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->waitForText('Holdene i holdrunden');

            $browser->on(new TeamFightEditPage())
                ->type('@player-search-input', 'Spiller Der Ikke Findes')
                ->waitForText('Ingen spillere fundet, som matcher')
                ->assertSeeIn('@missing-player-guide-link', 'Vejledning: Opret en spiller, der mangler')
                ->assertAttributeContains('@missing-player-guide-link', 'href', '/app/help/guides/opret-en-spiller-der-mangler-i-nembadminton');

            $browser->visit(new HelpGuidePage('opret-en-spiller-der-mangler-i-nembadminton'))
                ->assertSeeIn('@help-article', 'Opret en spiller, der mangler i Nembadminton')
                ->assertSeeIn('@help-article', 'Badmintonplayer ID');
        });
    }
}
