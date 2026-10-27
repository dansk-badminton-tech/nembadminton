<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundPlayoffParticipationTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_the_warning_shows_without_switching_it_on_and_does_not_block_saving(): void
    {
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        $round = $this->playoffRoundWithMember('1. division', $member);

        $this->browse(function (Browser $browser) use ($round, $member) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForTextIn('@playoff-participation-warnings', $member->name)
                ->assertSeeIn('@validation-playoff-participation', 'Advarsel')
                ->assertSeeIn('@playoff-participation-warnings', "Hold 1: {$member->name} har 0 af 3 kampe i sæsonen og har ikke spillet for klubben senest 20. oktober (§32 stk. 1).")
                ->assertPresent('@playoff-participation-guide')
                ->click('@settings-button')->waitFor('@settings-name')
                ->type('@settings-name', 'Saved despite playoff warning')->click('@settings-save')
                ->waitUntilMissing('@settings-modal')->refresh()->on(new TeamFightEditPage)
                ->waitForText('Saved despite playoff warning')
                ->waitForTextIn('@playoff-participation-warnings', $member->name)
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitForTextIn('@validation-playoff-participation', 'OK')
                ->assertMissing('@playoff-participation-warnings');
        });
    }

    public function test_the_status_is_hidden_where_the_requirement_cannot_apply(): void
    {
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        $round = $this->playoffRoundWithMember('2. division', $member);

        $this->browse(function (Browser $browser) use ($round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitFor('@team-table-section')
                ->assertMissing('@playoff-participation-control')
                ->assertMissing('@playoff-participation-warnings');
        });
    }

    private function playoffRoundWithMember(string $tier, Member $member): TeamRound
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier($tier)->create([
            'name' => 'Højbjerg 1', 'clubhouse_id' => $clubhouse->id, 'season_id' => 2026,
        ]);
        $round = TeamRound::query()->create([
            'name' => 'Slutspil', 'game_date' => '2027-01-11', 'version' => '2025-07-02',
            'season_id' => 2026, 'round' => 8, 'clubhouse_id' => $clubhouse->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ]);
        $squad = Squad::query()->create(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10]);
        $squad->categories()->create(['name' => '1. DS', 'category' => 'DS'])->players()->create([
            'member_ref_id' => $member->refId, 'name' => $member->name, 'gender' => $member->gender,
        ]);

        return $round;
    }
}
