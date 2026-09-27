<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightDashboardPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundEntryTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected string $seeder = 'TestingDataSeeder';

    public function test_populated_list_shows_round_data_and_opens_the_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouseId = Clubhouse::firstOrFail()->id;
            $dashboard = new TeamFightDashboardPage($clubhouseId);

            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($dashboard)
                ->on($dashboard)
                ->selectSeasonFilter('all')
                ->waitForRound('3x13 Kamps - Valid')
                ->assertRoundRowContainsText('3x13 Kamps - Valid', ['2025-07-14', 'Juli 2025'])
                ->openRound('3x13 Kamps - Valid')
                ->on(new TeamFightEditPage)
                ->waitForText('3x13 Kamps - Valid');
        });
    }

    public function test_each_season_filter_shows_only_its_rounds(): void
    {
        $clubhouseId = Clubhouse::firstOrFail()->id;
        $year = $this->currentSeasonStartYear();
        $this->addRound($clubhouseId, 'Current season journey', $year.'-07-14');
        $this->addRound($clubhouseId, 'Previous season journey', ($year - 1).'-07-14');
        $this->addRound($clubhouseId, 'Earlier season journey', ($year - 2).'-07-14');

        $this->browse(function (Browser $browser) use ($clubhouseId) {
            $dashboard = new TeamFightDashboardPage($clubhouseId);
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($dashboard)
                ->on($dashboard)
                ->selectSeasonFilter('current')
                ->waitForRound('Current season journey')
                ->waitForRoundToDisappear('Previous season journey')
                ->assertRoundNamesInclude('Current season journey')
                ->assertRoundNamesExclude('Previous season journey', 'Earlier season journey')
                ->selectSeasonFilter('previous')
                ->waitForRound('Previous season journey')
                ->waitForRoundToDisappear('Current season journey')
                ->assertRoundNamesExclude('Current season journey', 'Earlier season journey')
                ->selectSeasonFilter('rest')
                ->waitForRound('Earlier season journey')
                ->waitForRoundToDisappear('Previous season journey')
                ->assertRoundNamesExclude('Current season journey', 'Previous season journey')
                ->selectSeasonFilter('all')
                ->waitForRound('Current season journey')
                ->assertRoundNamesInclude('Current season journey', 'Previous season journey', 'Earlier season journey')
                ->selectSeasonFilter('current')
                ->waitForRound('Current season journey')
                ->waitForRoundToDisappear('Previous season journey')
                ->assertRoundNamesExclude('Previous season journey', 'Earlier season journey');
        });
    }

    public function test_sorting_and_pagination_change_the_visible_rounds(): void
    {
        $clubhouseId = Clubhouse::firstOrFail()->id;
        $year = $this->currentSeasonStartYear();
        foreach (range(1, 12) as $day) {
            $this->addRound($clubhouseId, sprintf('Pagination round %02d', $day), sprintf('%d-07-%02d', $year, $day));
        }

        $this->browse(function (Browser $browser) use ($clubhouseId) {
            $dashboard = new TeamFightDashboardPage($clubhouseId);
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($dashboard)
                ->on($dashboard)
                ->selectSeasonFilter('current')
                ->waitForRound('Pagination round 12')
                ->waitForRoundToDisappear('3x13 Kamps - Valid')
                ->assertFirstRound('Pagination round 12')
                ->sortByGameDate()
                ->waitForFirstRound('Pagination round 01')
                ->assertRoundNamesExclude('Pagination round 12')
                ->nextPage()
                ->waitForRound('Pagination round 12')
                ->assertRoundNamesExclude('Pagination round 01');
        });
    }

    public function test_empty_list_offers_creation(): void
    {
        TeamRound::query()->delete();

        $this->browse(function (Browser $browser) {
            $clubhouseId = Clubhouse::firstOrFail()->id;
            $dashboard = new TeamFightDashboardPage($clubhouseId);
            $create = new TeamFightCreatePage($clubhouseId);

            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($dashboard)
                ->on($dashboard)
                ->selectSeasonFilter('current')
                ->waitForEmptyList()
                ->assertSee('Kom i gang med din næste holdrunde planlægning her')
                ->openEmptyListCreation()
                ->on($create);
        });
    }

    public function test_creation_requires_valid_round_date_ranking_and_season(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouseId = Clubhouse::firstOrFail()->id;
            $create = new TeamFightCreatePage($clubhouseId);
            $dashboard = new TeamFightDashboardPage($clubhouseId);
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($create)
                ->on($create)
                ->waitUntilEnabled('@round-input')
                ->type('@name-input', 'Blocked entry journey')
                ->click('@submit-button')
                ->assertCreationBlocked()
                ->assertInvalidRound()
                ->setRound(0)
                ->click('@submit-button')
                ->assertCreationBlocked()
                ->assertInvalidRound()
                ->setRound(1)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Du mangler at sætte en spilledato')
                ->assertCreationBlocked()
                ->selectDate(7, 2025, 15)
                ->clearSeason()
                ->click('@submit-button')
                ->assertCreationBlocked()
                ->assertInvalidSeason()
                ->selectSeason(2025)
                ->clearRanking()
                ->click('@submit-button')
                ->assertCreationBlocked()
                ->assertInvalidRanking()
                ->visit($dashboard)
                ->on($dashboard)
                ->selectSeasonFilter('all')
                ->waitForRound('3x13 Kamps - Valid')
                ->assertRoundNamesExclude('Blocked entry journey');
        });
    }

    public function test_completing_creation_opens_the_new_round_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouseId = Clubhouse::firstOrFail()->id;
            $dashboard = new TeamFightDashboardPage($clubhouseId);
            $create = new TeamFightCreatePage($clubhouseId);

            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($dashboard)
                ->on($dashboard)
                ->click('@create-team-fight-link')
                ->on($create)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'New entry journey')
                ->setRound(2)
                ->selectDate(7, 2025, 15)
                ->selectSeason(2025)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->on(new TeamFightEditPage)
                ->waitForText('New entry journey')
                ->assertPathContains('/edit')
                ->visit($dashboard)
                ->on($dashboard)
                ->selectSeasonFilter('all')
                ->waitForRound('New entry journey')
                ->assertRoundRowContainsText('New entry journey', ['2', '2025-07-15', 'Juli 2025']);
        });
    }

    private function addRound(int $clubhouseId, string $name, string $date): void
    {
        TeamRound::query()->create([
            'name' => $name,
            'game_date' => $date,
            'version' => '2025-07-02',
            'round' => 1,
            'user_id' => 1,
            'clubhouse_id' => $clubhouseId,
        ]);
    }

    private function currentSeasonStartYear(): int
    {
        return now()->month >= 7 ? now()->year : now()->year - 1;
    }
}
