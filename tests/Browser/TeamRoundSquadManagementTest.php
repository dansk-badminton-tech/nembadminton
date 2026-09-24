<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundSquadManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_administrator_can_manage_squads_in_a_team_round(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::firstOrFail();
            $createPage = new TeamFightCreatePage($clubhouse->id);

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($createPage)
                ->on($createPage)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Squad management journey')
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage());

            $browser->add13KampsHold()
                ->waitForTextIn("[dusk='squad-0']", '1. MD')
                ->assertSeeIn("[dusk='squad-0']", '3. HD');
            $browser->addCustomSquad('Custom Squad', 'Kredsserie', [
                'mix' => 1,
                'womenSingles' => 1,
                'mensDoubles' => 1,
            ]);
            $browser->waitForTextIn("[dusk='squad-1']", 'Custom Squad')
                ->assertSeeIn("[dusk='squad-1']", '1. MD')
                ->assertSeeIn("[dusk='squad-1']", '1. DS')
                ->assertSeeIn("[dusk='squad-1']", '1. HD');

            $browser->assertAttributeContains("[dusk='move-squad-up-0']", 'class', 'is-disabled')
                ->assertAttributeContains("[dusk='move-squad-down-1']", 'class', 'is-disabled');
            $browser->openSquadAction(1, 'edit-squad')
                ->waitFor("[dusk='edit-squad-name-input']")
                ->clear("[dusk='edit-squad-name-input']")
                ->type("[dusk='edit-squad-name-input']", 'Edited Squad')
                ->clear("[dusk='edit-squad-tier-input']")
                ->type("[dusk='edit-squad-tier-input']", 'Danmarksserien')
                ->type("[dusk='edit-squad-playing-place-input']", 'Badmintonhallen')
                ->type("[dusk='edit-squad-playing-address-input']", 'Fjervej 1')
                ->type("[dusk='edit-squad-playing-zip-code-input']", '8000')
                ->type("[dusk='edit-squad-playing-city-input']", 'Aarhus C');

            $browser->script(<<<'JS'
                const field = document.querySelector("[dusk='edit-squad-ranking-field'] select");
                field.value = Array.from(field.options).find(option => option.textContent.includes('August 2025')).value;
                field.dispatchEvent(new Event('change', {bubbles: true}));
            JS);

            $browser->clear("[dusk='edit-squad-playing-datetime'] input")
                ->type("[dusk='edit-squad-playing-datetime'] input", '15.07.2025 19:30')
                ->keys("[dusk='edit-squad-playing-datetime'] input", '{tab}')
                ->click("[dusk='edit-squad-save-button']")
                ->waitUntilMissing("[dusk='edit-squad-name-input']")
                ->waitForTextIn("[dusk='squad-1']", 'Edited Squad')
                ->assertSeeIn("[dusk='squad-1']", 'Danmarksserien')
                ->assertSeeIn("[dusk='squad-1']", 'Badmintonhallen')
                ->assertSeeIn("[dusk='squad-1']", 'August 2025')
                ->assertSeeIn("[dusk='squad-1'] [dusk='squad-info-datetime']", '19.30');

            $browser->mouseover("[dusk='squad-1'] [dusk='squad-info-place']")
                ->waitForText('Fjervej 1')
                ->assertSee('8000')
                ->assertSee('Aarhus C')
                ->openSquadAction(1, 'edit-squad')
                ->waitFor("[dusk='edit-squad-name-input']")
                ->assertValue("[dusk='edit-squad-playing-datetime'] input", '15.7.2025, 19.30')
                ->click('.modal-card-head .delete')
                ->waitUntilMissing("[dusk='edit-squad-name-input']")
                ->waitUntilMissing('.snackbar');

            $browser->openSquadAction(1, 'move-squad-up')
                ->waitForTextIn("[dusk='squad-0']", 'Edited Squad')
                ->assertSeeIn("[dusk='squad-1']", '3. HD')
                ->assertDontSeeIn("[dusk='squad-1']", 'Edited Squad')
                ->openSquadAction(0, 'delete-squad')
                ->waitFor('.dialog')
                ->click('.dialog .modal-card-foot .button:first-child')
                ->assertSeeIn("[dusk='squad-0']", 'Edited Squad')
                ->openSquadAction(0, 'delete-squad')
                ->waitFor('.dialog')
                ->click('.dialog .modal-card-foot .button:last-child')
                ->waitUntilMissing("[dusk='squad-1']")
                ->assertDontSee('Edited Squad');
        });
    }
}
