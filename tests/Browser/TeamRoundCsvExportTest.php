<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundCsvExportTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_administrator_can_export_the_official_lineup_while_a_scenario_is_selected(): void
    {
        $this->browse(function (Browser $browser) {
            $createPage = new TeamFightCreatePage(Clubhouse::firstOrFail()->id);

            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($createPage)
                ->on($createPage)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'CSV export journey')
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage);

            $browser->addCustomSquad('Export Squad', 'Kredsserie', ['womenSingles' => 1, 'womenDoubles' => 1])
                ->waitForTextIn("[dusk='squad-0']", '1. DD')
                ->fillCategorySlot(0, '1. DS', 'Spela Silvester Laumand')
                ->fillCategorySlot(0, '1. DD', 'Spela Silvester Laumand')
                ->fillCategorySlot(0, '1. DD', 'Michella Skov')
                ->assertAllSlotsFilled();

            $browser->createScenarioFromOfficial('Plan B')
                ->assertScenarioSelected('Plan B', false)
                ->removeMemberFromCategory(0, '1. DS', 'Spela Silvester Laumand')
                ->fillCategorySlot(0, '1. DS', 'Karoline Keller Rolsted')
                ->waitForTextIn('@team-table-section', 'Karoline Keller Rolsted');

            $browser->assertScenarioSelected('Plan B', false)
                ->assertCsvExport(true, [
                    '"Hold 1"',
                    '"1. DS","Spela Silvester Laumand"',
                    '"1. DD","Spela Silvester Laumand"',
                    ',"Michella Skov"',
                ])
                ->assertCsvExport(false, [
                    '"Hold 1"',
                    '"Spela Silvester Laumand"',
                    '"Michella Skov"',
                ]);
        });
    }
}
