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

class TeamRoundFinalRoundEligibilityTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_eligibility_warning_does_not_block_saving_or_editing_the_lineup(): void
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier('2. division')->create([
            'name' => 'Højbjerg 1', 'clubhouse_id' => $clubhouse->id, 'season_id' => 2026,
        ]);
        $round = TeamRound::query()->create([
            'name' => 'Final round eligibility', 'game_date' => '2026-10-11', 'version' => '2025-07-02',
            'season_id' => 2026, 'round' => 11, 'clubhouse_id' => $clubhouse->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ]);
        $squad = Squad::query()->create(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10]);
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        $squad->categories()->create(['name' => '1. DS', 'category' => 'DS'])->players()->create([
            'member_ref_id' => $member->refId, 'name' => $member->name, 'gender' => $member->gender,
        ]);

        $this->browse(function (Browser $browser) use ($round, $member) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->assertSeeIn('@final-round-warnings', 'har ikke stået på samme eller et lavere hold tidligere i sæsonen (§38 stk. 8)')
                ->click('@settings-button')->waitFor('@settings-name')
                ->type('@settings-name', 'Saved despite eligibility warning')->click('@settings-save')
                ->waitUntilMissing('@settings-modal')->refresh()->on(new TeamFightEditPage)
                ->waitForText('Saved despite eligibility warning')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitUntilMissing('@final-round-warnings')->refresh()->on(new TeamFightEditPage)
                ->waitFor('@team-table-section')->assertDontSeeIn('@team-table-section', $member->name)
                ->assertMissing('@final-round-warnings');
        });
    }
}
