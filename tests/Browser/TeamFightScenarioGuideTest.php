<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamFightScenarioGuideTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_scenario_controls_link_to_the_scenario_guide(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();
            $teamRound = TeamRound::where('name', '3x13 Kamps - Valid')->first();

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($clubhouse->id, $teamRound->id))
                ->assertSeeIn('@scenario-guide-link', 'Sådan arbejder du med scenarier')
                ->click('@scenario-guide-link')
                ->waitForLocation('/app/help/guides/arbejd-med-scenarier-i-en-holdrunde')
                ->waitForText('Arbejd med scenarier i en holdrunde');
        });
    }
}
