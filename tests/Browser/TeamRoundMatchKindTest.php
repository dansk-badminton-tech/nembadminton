<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundMatchKindTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_match_kind_is_optional_clearable_and_only_shown_in_the_relevant_context(): void
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier('Badmintonligaen')->create([
            'clubhouse_id' => $clubhouse->id, 'season_id' => 2026,
        ]);
        $round = TeamRound::query()->create([
            'name' => 'Kampart', 'game_date' => '2027-04-10', 'version' => '2025-07-02', 'season_id' => 2026,
            'round' => 12, 'clubhouse_id' => $clubhouse->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ]);
        $squad = Squad::query()->create(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10]);
        $squad->categories()->create(['name' => '1. HS', 'category' => 'HS']);

        $this->browse(function (Browser $browser) use ($clubhouse, $round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($clubhouse->id, $round->id))
                ->openSquadAction(0, 'edit-squad')
                ->waitFor("[dusk='edit-squad-match-kind'] select")
                ->assertSee('Vælg kampart for at fastslå sæsonfasen.')
                ->click("[dusk='edit-squad-save-button']")
                ->waitUntilMissing("[dusk='edit-squad-save-button']")
                ->waitUntilMissing('.snackbar', 10);

            foreach (['MEDAL_FINAL', 'QUALIFICATION', ''] as $kind) {
                $browser->openSquadAction(0, 'edit-squad')
                    ->waitFor("[dusk='edit-squad-match-kind'] select")
                    ->select("[dusk='edit-squad-match-kind'] select", $kind)
                    ->click("[dusk='edit-squad-save-button']")
                    ->waitUntilMissing("[dusk='edit-squad-save-button']")
                    ->waitUntilMissing('.snackbar', 10)
                    ->openSquadAction(0, 'edit-squad')
                    ->waitFor("[dusk='edit-squad-match-kind'] select")
                    ->assertSelected("[dusk='edit-squad-match-kind'] select", $kind)
                    ->click("[dusk='edit-squad-save-button']")
                    ->waitUntilMissing("[dusk='edit-squad-save-button']")
                    ->waitUntilMissing('.snackbar', 10);
            }
            $browser->openSquadAction(0, 'edit-squad')
                ->waitFor("[dusk='edit-squad-match-kind'] select")
                ->click("[dusk='edit-squad-paper-squad-switch']")
                ->assertMissing("[dusk='edit-squad-match-kind']")
                ->assertDontSee('Vælg kampart for at fastslå sæsonfasen.')
                ->click("[dusk='edit-squad-paper-squad-switch']")
                ->waitFor("[dusk='edit-squad-match-kind'] select")
                ->click("[dusk='disconnect-squad-team']")
                ->type("[dusk='edit-squad-tier-input']", '1. division')
                ->assertMissing("[dusk='edit-squad-match-kind']")
                ->type("[dusk='edit-squad-tier-input']", 'Badmintonligaen reserve')
                ->assertMissing("[dusk='edit-squad-match-kind']")
                ->type("[dusk='edit-squad-tier-input']", 'Badmintonligaen - slutspil 2026')
                ->waitFor("[dusk='edit-squad-match-kind'] select")
                ->type("[dusk='edit-squad-tier-input']", 'Badmintonligaen')
                ->waitFor("[dusk='edit-squad-match-kind'] select")
                ->click("[dusk='edit-squad-save-button']")
                ->waitUntilMissing("[dusk='edit-squad-save-button']")
                ->changeRoundNumber(11)
                ->waitUntilMissing('.snackbar', 10)
                ->openSquadAction(0, 'edit-squad')
                ->waitFor("[dusk='edit-squad-tier-input']")
                ->assertMissing("[dusk='edit-squad-match-kind']");
        });
    }
}
