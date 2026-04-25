<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

/**
 * Test constructing a holdrunde (team match round) with 3x 13-kamps hold (13-match teams).
 *
 * Each 13-kamps hold has 13 categories across one squad:
 *   1.MD, 2.MD, 1.DS, 2.DS, 1.HS, 2.HS, 3.HS, 4.HS, 1.DD, 2.DD, 1.HD, 2.HD, 3.HD
 *
 * Each squad requires 10 unique players (4 women + 6 men) who each appear in both
 * a singles category and a doubles category:
 *   - Women appear in MD/DS (singles) AND DD (doubles)
 *   - Men appear in MD/HS (singles) AND HD (doubles)
 *
 * All player-to-category assignments are hardcoded in getSquadSlots() and filled
 * entirely via inline autocomplete search — no ranking list / "+" button used.
 * To change who plays where, edit getSquadSlots().
 */
class TeamFightConstruct3x13KampsTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    /**
     * Return the hardcoded player assignments for ALL categories across all 3 squads.
     *
     * Each squad maps category name → [player1] or [player1, player2].
     *   - MD (mixed doubles): 2 players (1 woman + 1 man)
     *   - DS (dame single):   1 player (woman)
     *   - HS (herre single):  1 player (man)
     *   - DD (dame double):   2 players (both women)
     *   - HD (herre double):  2 players (both men)
     *
     * Categories are filled in DOM order within each squad's table.
     * Within each category, the first player in the array fills the first empty
     * slot, the second player fills the second slot.
     *
     * ┌──────────────────────────────────────────────────────────────────────┐
     * │ TO CHANGE PLAYER ASSIGNMENTS:                                       │
     * │ Edit the player names below. Players must exist in the seeded data  │
     * │ (Juli 2025 rangliste). Each player appears once in a singles        │
     * │ category (MD/DS/HS) and once in a doubles category (DD/HD).         │
     * │                                                                     │
     * │ Gender rules:                                                       │
     * │   MD = [woman, man]  DS = [woman]  HS = [man]                       │
     * │   DD = [woman, woman]  HD = [man, man]                              │
     * └──────────────────────────────────────────────────────────────────────┘
     */
    private function getSquadSlots(): array
    {
        return [
            // ── Squad 1 (Hold 1) ──
            // Women: Josefine Eggert Jackson (DD:2940), Sarah Berthelsen (DD:2652),
            //        Spela Silvester Laumand (DD:2680), Karoline Keller Rolsted (DD:2918)
            // Men:   Patrick Buhl (HD:3600), Frederik Weibøl (HD:3224),
            //        Kaj Lü (HD:3403), Magnus Schøtt Jensen (HD:3076),
            //        Mads Sloth (HD:3245), Nikolai Andersson (HD:3138)
            [
                '1. MD' => ['Josefine Eggert Jackson', 'Patrick Buhl'],
                '2. MD' => ['Sarah Berthelsen', 'Frederik Weibøl'],
                '1. DS' => ['Spela Silvester Laumand'],
                '2. DS' => ['Karoline Keller Rolsted'],
                '1. HS' => ['Kaj Lü'],
                '2. HS' => ['Magnus Schøtt Jensen'],
                '3. HS' => ['Mads Sloth'],
                '4. HS' => ['Nikolai Andersson'],
                '1. DD' => ['Josefine Eggert Jackson', 'Sarah Berthelsen'],
                '2. DD' => ['Spela Silvester Laumand', 'Karoline Keller Rolsted'],
                '1. HD' => ['Patrick Buhl', 'Frederik Weibøl'],
                '2. HD' => ['Kaj Lü', 'Magnus Schøtt Jensen'],
                '3. HD' => ['Mads Sloth', 'Nikolai Andersson'],
            ],

            // ── Squad 2 (Hold 2) ──
            // Women: Mette Juul Christensen (DD:2843), Caroline Koborg (DD:2389),
            //        Tanja Damsgaard (DD:2672), Signe Markwall (DD:2448)
            // Men:   Thomas Iversen (HD:3285), Kaare B. Mejding (HD:3386),
            //        Vitus Dyrløv Fensholt (HD:2573), Thomas Kyhl Sommer (HD:3122),
            //        Oliver Ousted (HD:3006), Jesper Laumand (HD:3172)
            [
                '1. MD' => ['Mette Juul Christensen', 'Thomas Iversen'],
                '2. MD' => ['Caroline Koborg', 'Kaare B. Mejding'],
                '1. DS' => ['Tanja Damsgaard'],
                '2. DS' => ['Signe Markwall'],
                '1. HS' => ['Vitus Dyrløv Fensholt'],
                '2. HS' => ['Thomas Kyhl Sommer'],
                '3. HS' => ['Oliver Ousted'],
                '4. HS' => ['Jesper Laumand'],
                '1. DD' => ['Mette Juul Christensen', 'Caroline Koborg'],
                '2. DD' => ['Tanja Damsgaard', 'Signe Markwall'],
                '1. HD' => ['Thomas Iversen', 'Kaare B. Mejding'],
                '2. HD' => ['Jesper Laumand', 'Thomas Kyhl Sommer'],
                '3. HD' => ['Oliver Ousted', 'Vitus Dyrløv Fensholt'],
            ],

            // ── Squad 3 (Hold 3) ──
            // Women: Emma Sejersen (DD:2756), Hanne Juul Christensen (DD:2594),
            //        Amanda M. Sommer, Katrine Tingleff
            // Men:   Simon Dømmestrup (HD:3149), Per Christophersen (HD:3168),
            //        Mathias Juul Hornbøll (HD:2858), Nikolaj Zawadzki (HD:2954),
            //        Frederik Balmer Odgaard (HD:3019), Lau Viskum Nielsen (HD:3087)
            [
                '1. MD' => ['Emma Sejersen', 'Simon Dømmestrup'],
                '2. MD' => ['Hanne Juul Christensen', 'Per Christophersen'],
                '1. DS' => ['Amanda M. Sommer'],
                '2. DS' => ['Katrine Tingleff'],
                '1. HS' => ['Mathias Juul Hornbøll'],
                '2. HS' => ['Nikolaj Zawadzki'],
                '3. HS' => ['Frederik Balmer Odgaard'],
                '4. HS' => ['Lau Viskum Nielsen'],
                '1. DD' => ['Emma Sejersen', 'Hanne Juul Christensen'],
                '2. DD' => ['Amanda M. Sommer', 'Katrine Tingleff'],
                '1. HD' => ['Simon Dømmestrup', 'Per Christophersen'],
                '2. HD' => ['Nikolaj Zawadzki', 'Lau Viskum Nielsen'],
                '3. HD' => ['Frederik Balmer Odgaard', 'Mathias Juul Hornbøll'],
            ],
        ];
    }

    public function test_user_can_construct_holdrunde_with_3x_13_kamps_hold(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            // ── Step 1: Login ──
            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234');

            // ── Step 2: Create holdrunde ──
            $createPage = new TeamFightCreatePage($clubhouse->id);
            $browser->visit($createPage)
                ->on($createPage);

            $browser->waitUntilEnabled('@name-input')
                ->type('@name-input', '3x13 Kamps Holdrunde Test');

            $browser->on($createPage)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025');

            $browser->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->assertPathContains('/team-fight/')
                ->assertPathContains('/edit')
                ->waitForText('Holdene i holdrunden')
                ->waitForText('3x13 Kamps Holdrunde Test');

            // Register TeamFightEditPage macros so we can call page object methods
            $browser->on(new TeamFightEditPage());

            // ── Step 3: Add 3x 13-kamps hold ──
            for ($i = 0; $i < 3; $i++) {
                $browser->add13KampsHold();
                $browser->waitForText('Hold ' . ($i + 1));
            }

            for ($i = 1; $i <= 3; $i++) {
                $browser->assertSee("Hold {$i}");
            }

            $browser->screenshot('3x13kamps-squads-added');

            // =====================================================================
            // Step 4: Fill all 3 squads via inline autocomplete search.
            //
            // Every player is placed directly into their category slot — no ranking
            // list or "+" button used. All assignments come from getSquadSlots().
            //
            // To change who plays where, edit getSquadSlots().
            // =====================================================================

            $allSquadSlots = $this->getSquadSlots();

            for ($squadIdx = 0; $squadIdx < 3; $squadIdx++) {
                $squadNum = $squadIdx + 1;
                $browser->screenshot("3x13kamps-squad-{$squadNum}-before");

                $browser->fillSquad($squadIdx, $allSquadSlots[$squadIdx]);

                $browser->screenshot("3x13kamps-squad-{$squadNum}-filled");
            }

            // ── Step 5: Verify all squads are fully populated ──
            for ($i = 1; $i <= 3; $i++) {
                $browser->assertSee("Hold {$i}");
            }

            $browser->assertAllSlotsFilled();

            // ── Step 6: Verify validation status indicators ──
            $browser->assertValidationPassing();

            $browser->screenshot('3x13kamps-test-complete');
        });
    }
}
