<?php

namespace Tests\Browser;

use App\Models\TeamRound;
use Database\Seeders\YouthTeamRoundSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

/**
 * How Youth Player (U15/U17/U19) conflicts show on the seeded
 * "Ungdom - 3. runde (konflikter)" holdrunde (see YouthTeamRoundSeeder).
 *
 * Today Youth Players are not validated on points (§38 stk. 5): their
 * conflicts are marked green instead of red, and none of them count as a
 * validation error. Update these assertions when #215 changes that.
 */
class TeamFightYouthConflictsTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private function openConflictRound(Browser $browser): void
    {
        $teamRound = TeamRound::where('name', YouthTeamRoundSeeder::CONFLICT_ROUND)->sole();

        $browser->visit(new LoginPage)
            ->loginSPA('testing@gmail.com', 'Test1234')
            ->visit(new TeamFightEditPage($teamRound->clubhouse_id, $teamRound->id))
            ->waitForText('Holdene i holdrunden')
            ->waitForTextIn("[dusk='squad-1']", 'Mathilde Hay-Schmidt');
    }

    public function test_youth_conflicts_do_not_fail_the_validation(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openConflictRound($browser);

            $browser->assertAllValidationsOk();
        });
    }

    public function test_youth_player_above_a_stronger_senior_is_marked_green(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openConflictRound($browser);
            $browser->assertAllValidationsOk();

            // Aske Groth Jensen (U17, HS 2225) above Jakob Christensen (HS 2420)
            $browser->assertPlayerHighlight(0, '3. HS', 'Aske Groth Jensen', 'success')
                ->assertPlayerTooltipContains(0, '3. HS', 'Aske Groth Jensen', 'Spiller for højt på holdet i kategorien:')
                ->assertPlayerTooltipContains(0, '3. HS', 'Aske Groth Jensen', 'Jakob Christensen')
                ->assertPlayerHighlight(0, '4. HS', 'Jakob Christensen', null);

            // Lauge Almlund Højgaard (U19, HS 2677) below Jesper Lauge Andersen (HS 2422) is not flagged
            $browser->assertPlayerHighlight(0, '1. HS', 'Jesper Lauge Andersen', null)
                ->assertPlayerHighlight(0, '2. HS', 'Lauge Almlund Højgaard', null);
        });
    }

    public function test_senior_with_a_youth_partner_above_a_stronger_pair_is_marked_green(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openConflictRound($browser);
            $browser->assertAllValidationsOk();

            $browser->assertPlayerHighlight(0, '2. HD', 'Jesper Lauge Andersen', 'success')
                ->assertPlayerTooltipContains(0, '2. HD', 'Jesper Lauge Andersen', 'OBS: Har U15/U17/U19 makker')
                ->assertPlayerTooltipContains(0, '2. HD', 'Jesper Lauge Andersen', 'Victor R. Andersen')
                ->assertPlayerHighlight(0, '2. HD', 'Aske Groth Jensen', 'success')
                ->assertPlayerHighlight(0, '3. HD', 'Jakob Christensen', null);
        });
    }

    public function test_youth_player_on_a_lower_squad_is_not_compared_across_squads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openConflictRound($browser);
            $browser->assertAllValidationsOk();

            // Mathilde Hay-Schmidt (U19, DS 2113) on Hold 2 vs Nanna Reese (DS 1876) on Hold 1
            $browser->assertPlayerHighlight(1, '1. DS', 'Mathilde Hay-Schmidt', null)
                ->assertPlayerHighlight(0, '2. DS', 'Nanna Reese', null);
        });
    }
}
