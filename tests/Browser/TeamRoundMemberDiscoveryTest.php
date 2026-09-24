<?php

namespace Tests\Browser;

use App\Models\Cancellation;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundMemberDiscoveryTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private const FIRST_ROW = '@player-search-panel table tbody tr:first-child';
    private const MEMBERS_PER_PAGE = 15;

    // Top of the July 2025 women's singles list for the seeded clubhouse.
    private const TOP_WOMAN = ['refId' => '870114-15', 'name' => 'Spela Silvester Laumand'];
    // Last Member with a points value that cannot tie onto the second page.
    private const OTHER_WOMAN = ['refId' => '910128-22', 'name' => 'Michella Skov'];
    private const INACTIVE_WOMAN = ['refId' => '000510-02', 'name' => 'Tanja Damsgaard'];
    private const CANCELLED_WOMAN = ['refId' => '960206-04', 'name' => 'Karoline Keller Rolsted'];
    // Ranked 21st once the inactive and cancelled Members are excluded, with no tied points.
    private const PAGE_TWO_WOMAN = ['refId' => '020410-07', 'name' => 'Klara Krogh'];
    // Top of the July 2025 men's singles list.
    private const TOP_MAN = ['refId' => '960609-15', 'name' => 'Kaj Lü', 'points' => '3535'];

    public function test_administrator_can_find_members_with_search_filters_and_pagination(): void
    {
        Member::query()->where('refId', self::INACTIVE_WOMAN['refId'])->firstOrFail()->update(['inactive' => true]);

        $this->browse(function (Browser $browser) {
            $this->createTeamRound($browser, 'Member discovery journey');

            $teamRound = TeamRound::query()->where('name', 'Member discovery journey')->firstOrFail();
            $cancellationDate = Carbon::parse($teamRound->game_date)->toDateString();
            Cancellation::query()->create(['refId' => self::CANCELLED_WOMAN['refId'], 'team_round_id' => $teamRound->id])
                ->dates()->create(['date' => $cancellationDate]);

            $browser->refresh()
                ->on(new TeamFightEditPage())
                ->waitFor($this->availableMember(self::TOP_WOMAN))
                ->assertSeeIn(self::FIRST_ROW, self::TOP_WOMAN['name'])
                // Both rank in the top three by points, so they would be on this page if not filtered out.
                ->assertMissing($this->availableMember(self::INACTIVE_WOMAN))
                ->assertMissing($this->availableMember(self::CANCELLED_WOMAN));

            // Name search
            $browser->searchMembers('Silvester')
                ->waitUntilMissing($this->availableMember(self::OTHER_WOMAN))
                ->assertVisible($this->availableMember(self::TOP_WOMAN));
            $this->assertCount(1, $browser->elements("@player-search-panel [dusk^='available-player-']"));
            $browser->searchMembers('')
                ->waitFor($this->availableMember(self::OTHER_WOMAN));

            // Ranking category
            $browser->switchRankingList('MEN_SINGLE')
                ->waitForTextIn(self::FIRST_ROW, self::TOP_MAN['name'])
                ->assertSeeIn(self::FIRST_ROW, self::TOP_MAN['points'])
                ->assertMissing($this->availableMember(self::TOP_WOMAN))
                ->switchRankingList('WOMEN_SINGLE')
                ->waitForTextIn(self::FIRST_ROW, self::TOP_WOMAN['name']);

            // Pagination
            $browser->assertMissing($this->availableMember(self::PAGE_TWO_WOMAN))
                ->goToMemberSearchPage('next')
                ->waitFor($this->availableMember(self::PAGE_TWO_WOMAN))
                ->assertMissing($this->availableMember(self::TOP_WOMAN))
                ->assertMissing($this->availableMember(self::OTHER_WOMAN))
                ->assertSeeIn(self::FIRST_ROW . ' td:first-child', (string) (self::MEMBERS_PER_PAGE + 1))
                ->goToMemberSearchPage('previous')
                ->waitFor($this->availableMember(self::TOP_WOMAN))
                ->assertVisible($this->availableMember(self::OTHER_WOMAN))
                ->assertMissing($this->availableMember(self::PAGE_TWO_WOMAN));

            // Inactive filter
            $browser->searchMembers(self::INACTIVE_WOMAN['name'])
                ->waitForText('Ingen spillere fundet')
                ->toggleMemberFilter('inactive')
                ->waitFor($this->availableMember(self::INACTIVE_WOMAN))
                ->toggleMemberFilter('inactive')
                ->searchMembers('')
                ->waitFor($this->availableMember(self::TOP_WOMAN));

            // Afbud filter
            $browser->toggleMemberFilter('cancellation')
                ->waitFor($this->availableMember(self::CANCELLED_WOMAN))
                ->assertMissing($this->availableMember(self::TOP_WOMAN))
                ->click($this->availableMember(self::CANCELLED_WOMAN))
                ->waitForTextIn('@player-search-panel table', 'Afbuds datoer')
                ->assertSeeIn('@player-search-panel table', $cancellationDate)
                ->assertSeeIn('@player-search-panel table', 'Dig');
        });
    }

    public function test_administrator_can_create_a_local_member_and_correct_ranking_points(): void
    {
        $this->browse(function (Browser $browser) {
            $clubId = Clubhouse::firstOrFail()->clubs()->firstOrFail()->id;
            $localMember = ['refId' => '010199-99', 'name' => 'Lokal Testspiller'];

            $this->createTeamRound($browser, 'Local member journey');
            $browser->add13KampsHold()
                ->waitForTextIn("[dusk='squad-0']", '1. MD');

            $browser->createLocalMember($localMember['refId'], $localMember['name'], 'MEN', $clubId, ['SINGLE' => 4000])
                ->switchRankingList('MEN_SINGLE')
                ->searchMembers($localMember['name'])
                ->waitFor($this->availableMember($localMember))
                ->assertSeeIn(self::FIRST_ROW, '4000')
                ->addPlayersFromRankingList(1);

            $browser->waitForTextIn("[dusk='squad-0']", $localMember['name'])
                ->assertMissing("[dusk='squad-0'] @manual-correction-indicator")
                ->correctSquadMemberPoints(0, 'HS', 4100)
                ->waitFor("[dusk='squad-0'] @manual-correction-indicator");
        });
    }

    private function createTeamRound(Browser $browser, string $name): void
    {
        $createPage = new TeamFightCreatePage(Clubhouse::firstOrFail()->id);

        $browser->visit(new LoginPage())
            ->loginSPA('testing@gmail.com', 'Test1234')
            ->visit($createPage)
            ->on($createPage)
            ->waitUntilEnabled('@name-input')
            ->type('@name-input', $name)
            ->setRound(1)
            ->selectDate(7, 2025, 14)
            ->selectRankingByText('Juli 2025')
            ->click('@submit-button')
            ->waitForText('Dit hold er gemt')
            ->on(new TeamFightEditPage());
    }

    private function availableMember(array $member): string
    {
        return "[dusk='available-player-{$member['refId']}']";
    }
}
