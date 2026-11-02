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

class TeamRoundQualificationEligibilityTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_qualification_warning_explains_calendar_year_scope_and_does_not_block_saving_or_scenarios(): void
    {
        [$round, $team, $member] = $this->qualification();
        foreach ([1, 6] as $number) {
            $earlier = $round->replicate();
            $earlier->fill(['round' => $number, 'game_date' => '2027-01-10'])->save();
            $this->place($earlier, $team, $member);
        }
        $this->browse(function (Browser $browser) use ($round, $member) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('qualification', 'Slået fra')
                ->switchCheck('qualification')
                ->waitForCheckDetails('qualification', $member->name)
                ->assertSeeIn('@check-details-qualification', 'Hold 1')
                ->assertSeeIn('@check-details-qualification', '§27 stk. 5')
                ->assertSeeIn('@check-details-qualification', 'Nembadminton vurderer ikke kalenderårsalternativet')
                ->assertSeeIn('@check-status-qualification', 'Advarsel')
                ->click('@settings-button')->waitFor('@settings-name')
                ->type('@settings-name', 'Gemt med kvalifikationsadvarsel')->click('@settings-save')
                ->waitUntilMissing('@settings-modal')->refresh()->on(new TeamFightEditPage)
                ->waitForText('Gemt med kvalifikationsadvarsel')
                ->waitForCheckStatus('qualification', 'Slået fra')
                ->switchCheck('qualification')
                ->waitForCheckDetails('qualification', $member->name)
                ->createScenarioFromOfficial('Uden spilleren')
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitForCheckStatus('qualification', 'OK')
                ->assertDontSeeIn('@check-details-qualification', $member->name)
                ->selectScenario('Officiel opstilling')
                ->waitForCheckDetails('qualification', $member->name)
                ->changeRoundNumber(11)
                ->assertCheckNotApplicable('qualification', 'Gælder kun runde 12 i 2.–3. division og Danmarksserien.');
        });
    }

    public function test_incomparable_history_identifies_the_round_and_revalidation_replaces_uncertainty(): void
    {
        [$round, $team, $member] = $this->qualification();
        $earlier = $round->replicate();
        $earlier->fill(['round' => 7, 'name' => 'Forår uden holdlink'])->save();
        $squad = $this->place($earlier, $team, $member);
        $squad->update(['team_id' => null]);
        $this->browse(function (Browser $browser) use ($round, $team, $member, $squad, $earlier) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('qualification', 'Slået fra')
                ->switchCheck('qualification')
                ->waitForCheckDetails('qualification', 'kan ikke verificeres fuldt ud')
                ->assertSeeIn('@check-status-qualification', 'Kan ikke kontrolleres')
                ->assertSeeIn('@check-details-qualification', 'Forår uden holdlink (runde 7)')
                ->assertAttribute('[dusk="check-correction-qualification-'.$earlier->id.'"]', 'href', '/app/c-'.$round->clubhouse_id.'/team-fight/'.$earlier->id.'/edit');
            $squad->update(['team_id' => $team->id]);
            $other = $round->replicate();
            $other->fill(['round' => 1])->save();
            $this->place($other, $team, $member);
            $browser->click('@check-revalidate-qualification')->waitForCheckStatus('qualification', 'OK')
                ->assertDontSeeIn('@check-details-qualification', $member->name);
        });
    }

    private function qualification(): array
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier('2. division')->create([
            'name' => 'Højbjerg 1', 'clubhouse_id' => $clubhouse->id, 'season_id' => 2026,
        ]);
        $round = TeamRound::query()->create([
            'name' => 'Kvalifikation', 'game_date' => '2027-04-11', 'version' => '2025-07-02',
            'season_id' => 2026, 'round' => 12, 'clubhouse_id' => $clubhouse->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ]);
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        $this->place($round, $team, $member);

        return [$round, $team, $member];
    }

    private function place(TeamRound $round, Team $team, Member $member): Squad
    {
        $squad = Squad::query()->create(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10]);
        $squad->categories()->create(['name' => '1. DS', 'category' => 'DS'])->players()->create([
            'member_ref_id' => $member->refId, 'name' => $member->name, 'gender' => $member->gender,
        ]);

        return $squad;
    }
}
