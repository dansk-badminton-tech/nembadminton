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

    public function test_the_match_format_is_preselected_from_the_tier(): void
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()
            ->withTier(TournamentTier::query()->firstOrCreate(['tier_name' => '1. division']))
            ->create([
                'name' => 'Højbjerg 1',
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
                ->type('@name-input', 'Match format journey')
                ->setRound(12)
                ->selectDate(4, 2026, 18)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage);
            $teamRound = TeamRound::where('name', 'Match format journey')->sole();

            $browser->waitFor("[dusk='match-count-option-13'].is-link")
                ->assertMissing('@match-count-from-tier')
                ->selectSquadTeam($team->id)
                ->waitFor("[dusk='match-count-option-9'].is-link")
                ->assertSeeIn('@match-count-from-tier', 'Valgt ud fra niveau (1. division)')
                ->click("[dusk='match-count-option-13']")
                ->waitFor("[dusk='match-count-option-13'].is-link")
                ->assertMissing('@match-count-from-tier')
                ->deselectSquadTeam($team->id)
                ->assertPresent("[dusk='match-count-option-13'].is-link")
                ->selectSquadTeam($team->id)
                ->waitFor("[dusk='match-count-option-9'].is-link")
                ->submitSquadForm()
                ->waitForTextIn("[dusk='squad-0']", 'Højbjerg 1');
            $this->assertSame(9, $teamRound->squads()->sole()->categories()->count());

            // Round 12 is the qualification for 1. division, so 2. division plays 9 matches.
            $browser->click("[dusk='match-count-option-13']")
                ->startManualSquadEntry()
                ->type("[dusk='squad-tier-input']", '2. division')
                ->waitFor("[dusk='match-count-option-9'].is-link")
                ->assertSeeIn('@match-count-from-tier', 'Valgt ud fra niveau (2. division)');
        });
    }
}
