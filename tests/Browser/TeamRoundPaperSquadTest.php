<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundPaperSquadTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_coach_adds_a_squad_as_a_paper_squad_and_switches_it_off_again(): void
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier('Kredsserie')->create([
            'name' => 'Højbjerg 2',
            'clubhouse_id' => $clubhouse->id,
            'season_id' => 2025,
        ]);

        $this->browse(function (Browser $browser) use ($clubhouse, $team) {
            $createPage = new TeamFightCreatePage($clubhouse->id);
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($createPage)
                ->on($createPage)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Paper squad journey')
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage);
            $teamRound = TeamRound::query()->where('name', 'Paper squad journey')->sole();

            // Only a squad with a team can be a paper squad, so the switch waits for a team.
            $browser->waitFor("[dusk='squad-team-option-{$team->id}']")
                ->assertMissing("[dusk='squad-paper-squad-switch']")
                ->selectSquadTeam($team->id)
                ->waitFor("[dusk='squad-paper-squad-switch']")
                ->click("[dusk='squad-paper-squad-switch']")
                ->submitSquadForm()
                ->waitFor("[dusk='squad-0'] [dusk='squad-paper-squad-tag']")
                ->assertSeeIn("[dusk='squad-0'] [dusk='squad-paper-squad-tag']", 'Papirhold');
            $this->assertTrue(Squad::query()->where('team_round_id', $teamRound->id)->sole()->is_paper_squad);

            $browser->openSquadAction(0, 'edit-squad')
                ->waitFor("[dusk='edit-squad-paper-squad-switch']")
                ->assertChecked("[dusk='edit-squad-paper-squad-switch'] input")
                ->click("[dusk='edit-squad-paper-squad-switch']")
                ->assertNotChecked("[dusk='edit-squad-paper-squad-switch'] input")
                ->click("[dusk='edit-squad-save-button']")
                ->waitUntilMissing("[dusk='edit-squad-paper-squad-switch']")
                ->waitUntilMissing("[dusk='squad-0'] [dusk='squad-paper-squad-tag']");
            $this->assertFalse(Squad::query()->where('team_round_id', $teamRound->id)->sole()->is_paper_squad);
        });
    }
}
