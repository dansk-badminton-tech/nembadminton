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

class TeamRoundPaperSquadRuleTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private const MEMBER = ['refId' => '870114-15', 'name' => 'Spela Silvester Laumand'];

    public function test_the_paper_squad_rule_warns_on_a_paper_squad_and_not_on_a_regular_squad(): void
    {
        $squad = $this->roundTwoSquadWithAPlayerWhoHasNotPlayedThisSeason();

        $this->browse(function (Browser $browser) use ($squad) {
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($squad->teamRound->clubhouse_id, $squad->team_round_id))
                ->waitForTextIn("[dusk='validation-paper-squads']", 'Advarsel')
                ->assertSeeIn("[dusk='paper-squad-warnings']", 'Hold 1 (papirhold): '.self::MEMBER['name'].' har ikke spillet DS tidligere i sæsonen')
                ->assertSeeIn("[dusk='paper-squad-warnings']", '§54 stk. 7');

            $squad->update(['is_paper_squad' => false]);

            $browser->refresh()->on(new TeamFightEditPage)
                ->waitForTextIn('@team-table-section', self::MEMBER['name'])
                ->assertMissing("[dusk='validation-paper-squads']")
                ->assertMissing("[dusk='paper-squad-warnings']");
        });
    }

    /**
     * Round 2 of Season 2026, which has no earlier TeamRounds in the seed data, so the player has played nothing yet.
     */
    private function roundTwoSquadWithAPlayerWhoHasNotPlayedThisSeason(): Squad
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier('Kredsserie')->create([
            'name' => 'Højbjerg 2',
            'clubhouse_id' => $clubhouse->id,
            'season_id' => 2026,
        ]);
        $teamRound = TeamRound::query()->create([
            'name' => 'Paper squad rule journey',
            'game_date' => '2026-10-11',
            'version' => '2025-07-02',
            'season_id' => 2026,
            'round' => 2,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
            'clubhouse_id' => $clubhouse->id,
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'team_id' => $team->id,
            'is_paper_squad' => true,
            'order' => 1,
            'playerLimit' => 10,
        ]);
        $member = Member::query()->where('refId', self::MEMBER['refId'])->firstOrFail();
        $squad->categories()->create(['name' => '1. DS', 'category' => 'DS'])->players()->create([
            'member_ref_id' => $member->refId,
            'name' => $member->name,
            'gender' => $member->gender,
        ]);

        return $squad;
    }
}
