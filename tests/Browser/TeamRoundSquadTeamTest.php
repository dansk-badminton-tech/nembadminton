<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\TournamentTier;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundSquadTeamTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_administrator_can_attach_a_team_to_a_squad_and_the_squad_follows_the_teams_tier(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::firstOrFail();
            $createPage = new TeamFightCreatePage($clubhouse->id);

            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($createPage)
                ->on($createPage)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Attach team journey')
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage);

            $browser->addCustomSquad('Løst hold', 'Kredsserie', ['mix' => 1]);
            $browser->waitForTextIn("[dusk='squad-0']", 'Løst hold')
                ->assertSeeIn("[dusk='squad-0']", 'Kredsserie');

            $teamRound = TeamRound::query()->where('name', 'Attach team journey')->firstOrFail();
            $serie1 = TournamentTier::query()->firstOrCreate(['tier_name' => 'Serie 1']);
            $team = Team::factory()->withTier($serie1)->create([
                'name' => 'Højbjerg 1',
                'clubhouse_id' => $clubhouse->id,
                'season_id' => $teamRound->season_id,
            ]);

            $browser->openSquadAction(0, 'edit-squad')
                ->waitFor("[dusk='attach-squad-team-{$team->id}']")
                ->assertPresent("[dusk='edit-squad-tier-input']")
                ->click("[dusk='attach-squad-team-{$team->id}']")
                ->waitFor("[dusk='edit-squad-team-chip']")
                ->assertSeeIn("[dusk='edit-squad-team-chip']", 'Højbjerg 1 · Serie 1')
                ->assertMissing("[dusk='edit-squad-tier-input']")
                ->assertDisabled("[dusk='edit-squad-name-input']")
                ->click("[dusk='edit-squad-save-button']")
                ->waitUntilMissing("[dusk='edit-squad-name-input']")
                ->waitForTextIn("[dusk='squad-0']", 'Serie 1 Højbjerg 1')
                ->assertDontSeeIn("[dusk='squad-0'] h2", 'Kredsserie');

            $this->assertDatabaseHas('squads', [
                'team_round_id' => $teamRound->id,
                'team_id' => $team->id,
            ]);

            $danmarksserien = TournamentTier::query()->firstOrCreate(['tier_name' => 'Danmarksserien']);
            $team->update(['tier_id' => $danmarksserien->id]);

            $browser->refresh()
                ->waitForTextIn("[dusk='squad-0']", 'Danmarksserien Højbjerg 1');
        });
    }
}
