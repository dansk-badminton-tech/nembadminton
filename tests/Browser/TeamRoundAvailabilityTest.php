<?php

namespace Tests\Browser;

use App\Models\Cancellation;
use App\Models\CancellationCollector;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Point;
use App\Models\Squad;
use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundAvailabilityTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private const MEMBER = ['refId' => '870114-15', 'name' => 'Spela Silvester Laumand'];

    private const LINK_MEMBER = ['refId' => '910128-22', 'name' => 'Michella Skov'];

    private const ALLOCATED_MEMBER = ['refId' => '960206-04', 'name' => 'Karoline Keller Rolsted'];

    private const TOO_YOUNG_MEMBER = ['refId' => '120516-01', 'name' => 'Olivia Biering'];

    public function test_administrator_can_register_assign_and_remove_team_round_afbud(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTeamRoundWithSquad($browser, 'Afbud journey');

            $browser->searchMembers(self::MEMBER['name'])
                ->waitFor($this->availableMember(self::MEMBER))
                ->registerTeamRoundAfbud(self::MEMBER['refId'])
                ->waitUntilMissing($this->availableMember(self::MEMBER))
                ->toggleMemberFilter('cancellation')
                ->waitFor($this->availableMember(self::MEMBER))
                ->openCancellationDetails(self::MEMBER['refId'])
                ->waitForTextIn('@player-search-panel table', 'Dig')
                ->assertSeeIn('@player-search-panel table', '2025-07-14')
                ->assignCancelledMember(self::MEMBER['refId'])
                ->waitForTextIn('@team-table-section', self::MEMBER['name'])
                ->waitFor("[dusk='squad-0'] [dusk='cancellation-tag']")
                ->assertSeeIn("[dusk='squad-0'] [dusk='cancellation-tag']", 'Afbud');

            // Assigned Members leave the Available Member list, including its afbud view.
            $browser->removeSquadMember(0, self::MEMBER['name'])
                ->waitFor($this->availableMember(self::MEMBER))
                ->openCancellationDetails(self::MEMBER['refId'])
                ->waitForTextIn('@player-search-panel table', 'Dig')
                ->removeTeamRoundAfbud()
                ->waitUntilMissing($this->availableMember(self::MEMBER))
                ->toggleMemberFilter('cancellation')
                ->waitFor($this->availableMember(self::MEMBER))
                ->addPlayersFromRankingList(1)
                ->waitForTextIn('@team-table-section', self::MEMBER['name'])
                ->assertMissing("[dusk='squad-0'] [dusk='cancellation-tag']");
        });
    }

    public function test_administrator_can_set_and_remove_permanent_cancellation(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTeamRoundWithSquad($browser, 'Permanent cancellation journey');

            $browser->searchMembers(self::MEMBER['name'])
                ->waitFor($this->availableMember(self::MEMBER))
                ->toggleMemberFilter('cancellation')
                ->togglePermanentCancellationFilter()
                ->waitForTextIn('@player-search-panel', 'fandt 0.')
                ->toggleMemberFilter('cancellation')
                ->waitFor($this->availableMember(self::MEMBER))
                ->registerTeamRoundAfbud(self::MEMBER['refId'])
                ->toggleMemberFilter('cancellation')
                ->waitFor($this->availableMember(self::MEMBER))
                ->setPermanentCancellation(self::MEMBER['refId'])
                ->openCancellationDetails(self::MEMBER['refId'])
                ->waitForTextIn('@player-search-panel table', 'Spilleren er markeret som permanent afbud')
                ->removePermanentCancellation(self::MEMBER['refId'])
                ->waitForText('Permanent afbud slettet')
                ->waitForTextIn('@player-search-panel table', 'Dig')
                ->removeTeamRoundAfbud()
                ->toggleMemberFilter('cancellation')
                ->waitFor($this->availableMember(self::MEMBER));
        });
    }

    public function test_seeded_cancellation_link_and_parallel_allocation_are_visible_in_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTeamRoundWithSquad($browser, 'Availability warnings journey');
            $teamRound = TeamRound::query()->where('name', 'Availability warnings journey')->firstOrFail();

            $collector = CancellationCollector::query()->create([
                'email' => 'afbud@example.com',
                'user_id' => $teamRound->user_id,
                'clubhouse_id' => $teamRound->clubhouse_id,
            ]);
            $linkCancellation = new Cancellation(['refId' => self::LINK_MEMBER['refId'], 'email' => 'member@example.com']);
            $linkCancellation->cancellationCollector()->associate($collector);
            $linkCancellation->save();
            $linkCancellation->dates()->create(['date' => '2025-07-14']);

            $parallelRound = TeamRound::query()->create([
                'name' => 'Parallel Veteranrunde',
                'game_date' => $teamRound->game_date,
                'version' => $teamRound->version,
                'season_id' => $teamRound->season_id,
                'round' => $teamRound->round,
                'user_id' => $teamRound->user_id,
                'clubhouse_id' => $teamRound->clubhouse_id,
            ]);
            $parallelSquad = Squad::query()->create([
                'team_round_id' => $parallelRound->id,
                'name' => 'Veteranhold',
                'order' => 1,
                'playerLimit' => 10,
            ]);
            $category = $parallelSquad->categories()->create(['name' => '1. DS', 'category' => 'DS']);
            $member = Member::query()->where('refId', self::ALLOCATED_MEMBER['refId'])->firstOrFail();
            $category->players()->create([
                'member_ref_id' => $member->refId,
                'name' => $member->name,
                'gender' => $member->gender,
            ]);

            $browser->refresh()->on(new TeamFightEditPage)
                ->searchMembers(self::ALLOCATED_MEMBER['name'])
                ->waitFor($this->availableMember(self::ALLOCATED_MEMBER))
                ->waitForTextIn('@player-search-panel table', 'Optaget på: Parallel Veteranrunde')
                ->fillCategorySlot(0, '1. DS', self::ALLOCATED_MEMBER['name'])
                ->waitForTextIn("[dusk='squad-0'] [dusk='parallel-allocation-tag']", 'Parallel Veteranrunde')
                ->mouseover("[dusk='squad-0'] [dusk='parallel-allocation-tag']")
                ->waitForTextIn("[dusk='squad-0'] .b-tooltip.is-warning .tooltip-content", 'Parallel Veteranrunde - Veteranhold - 1. DS');

            $browser->removeSquadMember(0, self::ALLOCATED_MEMBER['name'])
                ->searchMembers(self::LINK_MEMBER['name'])
                ->toggleMemberFilter('cancellation')
                ->waitFor($this->availableMember(self::LINK_MEMBER))
                ->openCancellationDetails(self::LINK_MEMBER['refId'])
                ->waitForTextIn('@player-search-panel table', 'Afbudslink')
                ->assertCancellationRemovalDisabled()
                ->assignCancelledMember(self::LINK_MEMBER['refId'])
                ->waitForTextIn("[dusk='squad-0'] [dusk='cancellation-tag']", 'Afbud');
        });
    }

    public function test_player_under_15_by_end_of_season_start_year_gets_a_warning(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTeamRoundWithSquad($browser, 'Too young journey');
            // The seed data has no July 2025 ranking for this youth player.
            $tooYoung = Member::query()->where('refId', self::TOO_YOUNG_MEMBER['refId'])->firstOrFail();
            Point::query()->create([
                'member_id' => $tooYoung->id,
                'points' => 1075,
                'category' => 'DS',
                'vintage' => 'U15',
                'version' => '2025-07-02',
            ]);

            $browser->refresh()->on(new TeamFightEditPage)
                ->searchMembers(self::TOO_YOUNG_MEMBER['name'])
                ->waitFor($this->availableMember(self::TOO_YOUNG_MEMBER))
                ->fillCategorySlot(0, '1. DS', self::TOO_YOUNG_MEMBER['name'])
                ->waitForTextIn("[dusk='squad-0'] [dusk='too-young-tag']", 'Under 15 år')
                ->mouseover("[dusk='squad-0'] [dusk='too-young-tag']")
                ->waitForTextIn("[dusk='squad-0'] .b-tooltip.is-warning .tooltip-content", 'ikke fyldt 15 år senest 31.12.2025');

            $browser->removeSquadMember(0, self::TOO_YOUNG_MEMBER['name'])
                ->searchMembers(self::MEMBER['name'])
                ->waitFor($this->availableMember(self::MEMBER))
                ->fillCategorySlot(0, '1. DS', self::MEMBER['name'])
                ->waitForTextIn('@team-table-section', self::MEMBER['name'])
                ->assertMissing("[dusk='squad-0'] [dusk='too-young-tag']");
        });
    }

    private function createTeamRoundWithSquad(Browser $browser, string $name): void
    {
        $page = new TeamFightCreatePage(Clubhouse::firstOrFail()->id);
        $browser->visit(new LoginPage)
            ->loginSPA('testing@gmail.com', 'Test1234')
            ->visit($page)
            ->on($page)
            ->waitUntilEnabled('@name-input')
            ->type('@name-input', $name)
            ->setRound(1)
            ->selectDate(7, 2025, 14)
            ->selectRankingByText('Juli 2025')
            ->click('@submit-button')
            ->waitForText('Dit hold er gemt')
            ->on(new TeamFightEditPage)
            ->addCustomSquad('Availability Squad', 'Kredsserie', ['womenSingles' => 1])
            ->waitForTextIn("[dusk='squad-0']", '1. DS');
    }

    private function availableMember(array $member): string
    {
        return "[dusk='available-player-{$member['refId']}']";
    }
}
