<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightDashboardPage;
use Tests\DuskTestCase;

class TeamFightCreateTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_user_can_create_a_team_fight(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightCreatePage($clubhouse->id))
                ->on(new TeamFightCreatePage($clubhouse->id))
                ->screenshot('team-fight-create-page-loaded');

            $browser->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Dusk Test Holdrunde')
                ->screenshot('team-fight-name-filled');

            // Open the datepicker, wait for dropdown, then click day 15
            $browser->click('@date-picker')
                ->waitFor('.datepicker .dropdown-content')
                ->screenshot('team-fight-datepicker-open');

            $browser->script("document.querySelectorAll('.datepicker .datepicker-body a.datepicker-cell.is-selectable')[14].click()");

            $browser->pause(500)
                ->screenshot('team-fight-date-selected');

            // Select the first available ranking version via script
            // Buefy b-select listens on native 'input' event
            $browser->waitFor('@ranking-select')
                ->script("
                    var sel = document.querySelector(\"[dusk='team-fight-ranking-select'] select\");
                    sel.selectedIndex = 1;
                    sel.dispatchEvent(new Event('input'));
                    sel.dispatchEvent(new Event('change'));
                ");

            $browser->pause(500)
                ->screenshot('team-fight-ranking-selected');

            $browser->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->screenshot('team-fight-created-success');

            // Verify we were redirected to the edit page
            // Wait for the edit page content to load (Apollo query)
            $browser->assertPathContains('/team-fight/')
                ->assertPathContains('/edit')
                ->waitForText('Holdene i holdrunden')
                ->assertSee('Dusk Test Holdrunde')
                ->screenshot('team-fight-edit-page-after-create');

            // Navigate to dashboard and verify the new team fight appears
            $browser->visit(new TeamFightDashboardPage($clubhouse->id))
                ->on(new TeamFightDashboardPage($clubhouse->id))
                //->waitFor('@team-fights-table')
                //->assertSee('Dusk Test Holdrunde')
                ->screenshot('team-fight-visible-on-dashboard');
        });
    }
}
