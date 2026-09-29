<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\Team;
use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\Browser\Pages\TeamListPage;
use Tests\DuskTestCase;

class TeamRoundSquadTeamPickerTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_administrator_adds_squads_from_the_seasons_teams_or_without_a_team(): void
    {
        $clubhouse = Clubhouse::firstOrFail();
        $firstTeam = Team::factory()->withCustomTier('Kredsserie')->create([
            'name' => 'Højbjerg 1',
            'group_name' => 'Pulje 2',
            'clubhouse_id' => $clubhouse->id,
            'season_id' => 2025,
        ]);
        $secondTeam = Team::factory()->create([
            'name' => 'Højbjerg 2',
            'clubhouse_id' => $clubhouse->id,
            'season_id' => 2025,
        ]);
        $otherSeasonTeam = Team::factory()->create([
            'name' => 'Næste sæson',
            'clubhouse_id' => $clubhouse->id,
            'season_id' => 2026,
        ]);

        $this->browse(function (Browser $browser) use ($clubhouse, $firstTeam, $secondTeam, $otherSeasonTeam) {
            $teamRound = $this->createTeamRound($browser, $clubhouse->id, 'Team picker journey');

            $browser->waitFor("[dusk='squad-team-option-{$firstTeam->id}']")
                ->assertSeeIn("[dusk='squad-team-option-{$firstTeam->id}']", 'Højbjerg 1')
                ->assertDontSeeIn("[dusk='squad-team-option-{$firstTeam->id}']", 'Kredsserie')
                ->assertVisible("[dusk='squad-team-option-{$secondTeam->id}']")
                ->assertMissing("[dusk='squad-team-option-{$otherSeasonTeam->id}']")
                ->assertMissing("[dusk='squad-name-input']")
                ->assertMissing("[dusk='squad-tier-input']");
            $this->assertTrue($browser->script(<<<'JS'
                const teams = document.querySelector("[dusk='squad-team-section']");
                const matchCount = document.querySelector("[dusk='custom-match-count-toggle']");
                return Boolean(teams.compareDocumentPosition(matchCount) & Node.DOCUMENT_POSITION_FOLLOWING);
            JS)[0], 'Team buttons should come before Antal kampe');

            $browser->selectSquadTeam($firstTeam->id)
                ->assertSeeIn('@squad-team-chip', 'Højbjerg 1 · Kredsserie · Pulje 2')
                ->assertMissing("[dusk='squad-name-input']")
                ->assertMissing("[dusk='squad-0']");
            $this->assertSame(0, $teamRound->squads()->count());

            $browser->submitSquadForm()
                ->waitForTextIn("[dusk='squad-0']", 'Højbjerg 1')
                ->waitUntilMissing('@squad-team-chip');
            $this->assertSame($firstTeam->id, $teamRound->squads()->sole()->team_id);

            $browser->assertSquadTeamAdded($firstTeam->id)
                ->assertSquadTeamNotAdded($secondTeam->id)
                ->selectSquadTeam($firstTeam->id)
                ->assertSeeIn('@squad-team-chip', 'Højbjerg 1');

            $browser->startManualSquadEntry()
                ->assertMissing('@squad-team-chip')
                ->type("[dusk='squad-name-input']", 'Uden hold')
                ->submitSquadForm()
                ->waitForTextIn("[dusk='squad-1']", 'Uden hold');
            $this->assertNull($teamRound->squads()->where('name', 'Uden hold')->sole()->team_id);
        });
    }

    public function test_without_teams_in_the_season_the_form_points_to_the_teams_page(): void
    {
        $clubhouse = Clubhouse::firstOrFail();

        $this->browse(function (Browser $browser) use ($clubhouse) {
            $teamRound = $this->createTeamRound($browser, $clubhouse->id, 'No teams journey');

            $browser->waitFor('@squad-team-empty')
                ->assertSeeIn('@squad-team-empty', 'Opret jeres hold først, så udfyldes navn og niveau automatisk')
                ->addCustomSquad('Manuelt hold', 'Kredsserie', ['womenSingles' => 1])
                ->waitForTextIn("[dusk='squad-0']", 'Manuelt hold');
            $this->assertNull($teamRound->squads()->sole()->team_id);

            $browser->goToTeamsFromSquadForm()
                ->on(new TeamListPage($clubhouse->id));
        });
    }

    private function createTeamRound(Browser $browser, int $clubhouseId, string $name): TeamRound
    {
        $createPage = new TeamFightCreatePage($clubhouseId);

        $browser->visit(new LoginPage)
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
            ->on(new TeamFightEditPage);

        $teamRound = TeamRound::where('name', $name)->sole();
        $this->assertSame(2025, $teamRound->season_id);

        return $teamRound;
    }
}
