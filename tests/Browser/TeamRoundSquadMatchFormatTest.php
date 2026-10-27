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

class TeamRoundSquadMatchFormatTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_the_match_format_is_preselected_from_the_tier_when_adding_a_squad(): void
    {
        $clubhouse = Clubhouse::firstOrFail();
        $firstDivisionTeam = $this->team($clubhouse, 'Højbjerg 1', '1. division');
        $secondDivisionTeam = $this->team($clubhouse, 'Højbjerg 2', '2. division');
        $kredsTeam = $this->team($clubhouse, 'Højbjerg 3', 'Serie 1');

        $this->browse(function (Browser $browser) use ($clubhouse, $firstDivisionTeam, $secondDivisionTeam, $kredsTeam) {
            $createPage = new TeamFightCreatePage($clubhouse->id);
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($createPage)
                ->on($createPage)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Match format journey')
                // Round 12 of 2. division is the qualification for 1. division, played as 9 matches.
                ->setRound(12)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage);
            $teamRound = TeamRound::query()->where('name', 'Match format journey')->sole();

            $browser->waitFor("[dusk='squad-team-option-{$firstDivisionTeam->id}']")
                ->assertPresent($this->selectedMatchCount(13))
                ->assertMissing('@match-format-hint')
                ->selectSquadTeam($firstDivisionTeam->id)
                ->waitFor($this->selectedMatchCount(9))
                ->assertSeeIn('@match-format-hint', 'Valgt ud fra niveau (1. division)')
                ->click('@match-count-option-13')
                ->waitFor($this->selectedMatchCount(13))
                ->assertMissing('@match-format-hint')
                // Choosing a Team without a suggested format keeps the coach's pick.
                ->selectSquadTeam($kredsTeam->id)
                ->assertPresent($this->selectedMatchCount(13))
                ->assertMissing('@match-format-hint')
                ->selectSquadTeam($secondDivisionTeam->id)
                ->waitFor($this->selectedMatchCount(9))
                ->assertSeeIn('@match-format-hint', 'Valgt ud fra niveau (2. division)')
                // Deselecting the Team keeps the format.
                ->deselectSquadTeam($secondDivisionTeam->id)
                ->assertPresent($this->selectedMatchCount(9))
                ->assertMissing('@match-format-hint')
                ->selectSquadTeam($secondDivisionTeam->id)
                ->waitFor('@match-format-hint')
                ->submitSquadForm()
                ->waitForTextIn("[dusk='squad-0']", 'Højbjerg 2');
            $this->assertSame(9, $teamRound->squads()->sole()->categories()->count());

            $browser->click('@match-count-option-13')
                ->waitFor($this->selectedMatchCount(13))
                ->startManualSquadEntry()
                ->type("[dusk='squad-tier-input']", 'Badmintonligaen')
                ->waitFor($this->selectedMatchCount(9))
                ->assertSeeIn('@match-format-hint', 'Valgt ud fra niveau (Badmintonligaen)');
        });
    }

    private function team(Clubhouse $clubhouse, string $name, string $tierName): Team
    {
        return Team::factory()
            ->withTier(TournamentTier::query()->firstOrCreate(['tier_name' => $tierName]))
            ->create([
                'name' => $name,
                'clubhouse_id' => $clubhouse->id,
                'season_id' => 2025,
            ]);
    }

    private function selectedMatchCount(int $matchCount): string
    {
        return "[dusk='match-count-option-{$matchCount}'].is-link";
    }
}
