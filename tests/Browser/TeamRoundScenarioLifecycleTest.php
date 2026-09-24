<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundScenarioLifecycleTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_administrator_can_draft_promote_and_delete_scenarios(): void
    {
        $this->browse(function (Browser $browser) {
            $createPage = new TeamFightCreatePage(Clubhouse::firstOrFail()->id);

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($createPage)
                ->on($createPage)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Scenario lifecycle journey')
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage());

            $browser->addCustomSquad('Scenario Squad', 'Kredsserie', ['womenSingles' => 1])
                ->waitForTextIn("[dusk='squad-0']", '1. DS')
                ->fillCategorySlot(0, '1. DS', 'Spela Silvester Laumand')
                ->waitForTextIn('@team-table-section', 'Spela Silvester Laumand');

            $browser->createScenarioFromOfficial('Plan B')
                ->assertScenarioSelected('Plan B', false)
                ->assertSeeIn('@team-table-section', 'Spela Silvester Laumand')
                ->removeSquadMember(0, 'Spela Silvester Laumand')
                ->fillCategorySlot(0, '1. DS', 'Michella Skov')
                ->waitForTextIn('@team-table-section', 'Michella Skov')
                ->assertDontSeeIn('@team-table-section', 'Spela Silvester Laumand');

            $browser->selectScenario('Officiel opstilling')
                ->assertScenarioSelected('Officiel opstilling', true)
                ->waitForTextIn('@team-table-section', 'Spela Silvester Laumand')
                ->assertDontSeeIn('@team-table-section', 'Michella Skov')
                ->selectScenario('Plan B')
                ->assertScenarioSelected('Plan B', false)
                ->waitForTextIn('@team-table-section', 'Michella Skov')
                ->assertDontSeeIn('@team-table-section', 'Spela Silvester Laumand');

            $browser->renameScenario('Plan C')
                ->assertScenarioSelected('Plan C', false)
                ->cancelScenarioPromotion()
                ->selectScenario('Officiel opstilling')
                ->waitForTextIn('@team-table-section', 'Spela Silvester Laumand')
                ->assertDontSeeIn('@team-table-section', 'Michella Skov')
                ->selectScenario('Plan C')
                ->confirmScenarioPromotion()
                ->assertScenarioSelected('Plan C', true)
                ->waitForTextIn('@team-table-section', 'Michella Skov')
                ->selectScenario('Scenario lifecycle journey')
                ->assertScenarioSelected('Scenario lifecycle journey', false)
                ->waitForTextIn('@team-table-section', 'Spela Silvester Laumand')
                ->cancelScenarioDeletion()
                ->assertScenarioSelected('Scenario lifecycle journey', false)
                ->confirmScenarioDeletion()
                ->assertScenarioSelected('Plan C', true)
                ->waitForTextIn('@team-table-section', 'Michella Skov')
                ->assertDontSeeIn('@team-table-section', 'Spela Silvester Laumand')
                ->assertMissing('@scenario-selector-dropdown');
        });
    }
}
