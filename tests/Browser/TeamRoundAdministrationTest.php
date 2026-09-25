<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightDashboardPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundAdministrationTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_administrator_can_edit_copy_and_delete_a_team_round(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouseId = Clubhouse::firstOrFail()->id;
            $createPage = new TeamFightCreatePage($clubhouseId);
            $dashboardPage = new TeamFightDashboardPage($clubhouseId);

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($createPage)
                ->on($createPage)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Administration journey')
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage())
                ->waitForText('Administration journey');

            $browser->click('@settings-button')
                ->waitFor('@settings-modal')
                ->type('@settings-name', 'Edited administration journey')
                ->setRound(2)
                ->selectDate(8, 2025, 15)
                ->selectRankingByText('August 2025')
                ->click('@settings-save')
                ->waitUntilMissing('@settings-modal')
                ->waitForText('Holdrunden er gemt')
                ->waitForText('Edited administration journey')
                ->assertSee('Dato: 15.8.2025')
                ->assertSee('Rangliste: August 2025');

            $browser->addCustomSquad('Official Squad', 'Kredsserie', ['womenSingles' => 1])
                ->waitForTextIn("[dusk='squad-0']", '1. DS')
                ->fillCategorySlot(0, '1. DS', 'Spela Silvester Laumand')
                ->waitForTextIn('@team-table-section', 'Spela Silvester Laumand')
                ->createScenarioFromOfficial('Plan B')
                ->removeSquadMember(0, 'Spela Silvester Laumand')
                ->fillCategorySlot(0, '1. DS', 'Michella Skov')
                ->waitForTextIn('@team-table-section', 'Michella Skov');

            $browser->visit($dashboardPage)
                ->on($dashboardPage)
                ->press('Vis alle')
                ->waitForTextIn('@team-fights-table', 'Edited administration journey')
                ->assertSeeIn('@team-fights-table', '2025-08-15')
                ->assertSeeIn('@team-fights-table', 'August 2025')
                ->copyTeamRound('Edited administration journey')
                ->waitForTextIn('@team-fights-table', 'Kopi af Edited administration journey')
                ->assertSeeIn('@team-fights-table', 'Edited administration journey');

            $browser->clickLink('Kopi af Edited administration journey')
                ->on(new TeamFightEditPage())
                ->waitForTextIn("[dusk='squad-0']", 'Official Squad')
                ->assertSeeIn("[dusk='squad-0']", 'Kredsserie')
                ->assertSeeIn("[dusk='squad-0']", '1. DS')
                ->waitForTextIn('@team-table-section', 'Spela Silvester Laumand')
                ->assertDontSeeIn('@team-table-section', 'Michella Skov')
                ->assertMissing('@scenario-selector-dropdown')
                ->assertMissing('@scenario-draft-warning-banner')
                ->assertSee('Dato: 15.8.2025')
                ->assertSee('Rangliste: August 2025')
                ->click('@settings-button')
                ->waitFor('@settings-modal')
                ->assertValue('@settings-name', 'Kopi af Edited administration journey')
                ->assertValue('@settings-round', '2')
                ->waitUsing(10, 100, fn () => $browser->value('@settings-ranking') === '2025-08-02')
                ->assertValue('@settings-ranking', '2025-08-02')
                ->click('@settings-close');

            $browser->visit($dashboardPage)
                ->on($dashboardPage)
                ->press('Vis alle')
                ->waitForTextIn('@team-fights-table', 'Kopi af Edited administration journey')
                ->deleteTeamRound('Kopi af Edited administration journey', false)
                ->assertSeeIn('@team-fights-table', 'Kopi af Edited administration journey')
                ->deleteTeamRound('Kopi af Edited administration journey', true)
                ->waitUntilMissingText('Kopi af Edited administration journey')
                ->assertSeeIn('@team-fights-table', 'Edited administration journey');
        });
    }
}
