<?php

namespace Tests\Browser;

use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\TeamFightPublicPage;
use Tests\DuskTestCase;

class TeamFightPublicViewTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected string $seeder = 'TestingDataSeeder';

    public function test_public_team_fight_link_renders_team_round(): void
    {
        $this->browse(function (Browser $browser) {
            $teamRound = TeamRound::where('name', '3x13 Kamps - Valid')->firstOrFail();

            $browser->visit(new TeamFightPublicPage($teamRound->id))
                ->on(new TeamFightPublicPage($teamRound->id))
                ->assertPresent('@game-date')
                ->assertPresent('@squad')
                ->assertMissing('@not-found');
        });
    }

    public function test_public_team_fight_link_shows_not_found_for_unknown_id(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new TeamFightPublicPage('does-not-exist'))
                ->on(new TeamFightPublicPage('does-not-exist'))
                ->assertPresent('@not-found');
        });
    }
}
