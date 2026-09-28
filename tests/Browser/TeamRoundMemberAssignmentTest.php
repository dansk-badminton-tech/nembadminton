<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundMemberAssignmentTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private const SPELA = ['refId' => '870114-15', 'name' => 'Spela Silvester Laumand'];

    private const MICHELLA = ['refId' => '910128-22', 'name' => 'Michella Skov'];

    public function test_administrator_can_assign_and_reuse_members_across_scenarios(): void
    {
        $this->browse(function (Browser $browser) {
            $page = new TeamFightCreatePage(Clubhouse::firstOrFail()->id);
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($page)
                ->on($page)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Member assignment journey')
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage);

            $browser->addCustomSquad('First Squad', 'Kredsserie', ['womenSingles' => 1, 'womenDoubles' => 1])
                ->waitForTextIn("[dusk='squad-0']", '1. DD')
                ->add13KampsHold()
                ->waitForTextIn("[dusk='squad-1']", '1. DD');

            $browser->searchMembers(self::SPELA['name'])
                ->waitForAvailableMember(self::SPELA['refId'])
                ->addAvailableMember(self::SPELA['refId'])
                ->waitForTextIn("[dusk='squad-0'] tbody tr:first-child", self::SPELA['name'])
                ->waitUntilAvailableMemberMissing(self::SPELA['refId']);

            // Inline autocomplete permits reuse within the same Squad even though the left list does not.
            $browser->assertInlineMemberSuggestion(0, '1. DD', self::SPELA['name'], true)
                ->fillCategorySlot(0, '1. DD', self::SPELA['name'])
                ->removeMemberFromCategory(0, '1. DS', self::SPELA['name'])
                ->assertAvailableMemberMissing(self::SPELA['refId'])
                ->removeMemberFromCategory(0, '1. DD', self::SPELA['name'])
                ->waitForAvailableMember(self::SPELA['refId'])
                ->fillCategorySlot(1, '1. DS', self::SPELA['name'])
                ->waitUntilAvailableMemberMissing(self::SPELA['refId']);

            $browser->createScenarioFromOfficial('Plan B')
                ->assertScenarioSelected('Plan B', false)
                ->removeSquadMember(1, self::SPELA['name'])
                ->waitForAvailableMember(self::SPELA['refId'])
                ->fillCategorySlot(0, '1. DS', self::MICHELLA['name'])
                ->searchMembers(self::MICHELLA['name'])
                ->waitForTextIn('@player-search-panel', 'Ingen spillere fundet, som matcher "Michella Skov"')
                ->waitUntilAvailableMemberMissing(self::MICHELLA['refId'])
                ->assertInlineMemberSuggestion(0, '1. DD', self::MICHELLA['name'], true);

            $browser->selectScenario('Officiel opstilling')
                ->assertScenarioSelected('Officiel opstilling', true)
                ->waitForTextIn("[dusk='squad-1']", self::SPELA['name'])
                ->assertDontSeeIn('@team-table-section', self::MICHELLA['name'])
                ->waitForAvailableMember(self::MICHELLA['refId'])
                ->searchMembers(self::SPELA['name'])
                ->waitForTextIn('@player-search-panel', 'Ingen spillere fundet, som matcher "Spela Silvester Laumand"')
                ->waitUntilAvailableMemberMissing(self::SPELA['refId'])
                ->assertInlineMemberSuggestion(1, '1. DD', self::SPELA['name'], true)
                ->assertInlineMemberSuggestion(0, '1. DS', self::MICHELLA['name'], false);

            $browser->selectScenario('Plan B')
                ->assertScenarioSelected('Plan B', false)
                ->waitForTextIn("[dusk='squad-0']", self::MICHELLA['name'])
                ->assertDontSeeIn('@team-table-section', self::SPELA['name'])
                ->waitForAvailableMember(self::SPELA['refId'])
                ->assertInlineMemberSuggestion(1, '1. DD', self::SPELA['name'], false)
                ->assertInlineMemberSuggestion(0, '1. DD', self::MICHELLA['name'], true);

            // After Promotion the Official Lineup has a Scenario ID, rather than legacy unscoped slots.
            $browser->confirmScenarioPromotion()
                ->assertScenarioSelected('Plan B', true)
                ->searchMembers(self::MICHELLA['name'])
                ->waitForTextIn('@player-search-panel', 'Ingen spillere fundet, som matcher "Michella Skov"')
                ->waitUntilAvailableMemberMissing(self::MICHELLA['refId'])
                ->selectScenario('Member assignment journey')
                ->assertScenarioSelected('Member assignment journey', false)
                ->waitForAvailableMember(self::MICHELLA['refId'])
                ->searchMembers(self::SPELA['name'])
                ->waitForTextIn('@player-search-panel', 'Ingen spillere fundet, som matcher "Spela Silvester Laumand"')
                ->waitUntilAvailableMemberMissing(self::SPELA['refId'])
                ->selectScenario('Plan B')
                ->assertScenarioSelected('Plan B', true)
                ->waitForAvailableMember(self::SPELA['refId'])
                ->assertInlineMemberSuggestion(0, '1. DD', self::MICHELLA['name'], true);
        });
    }
}
