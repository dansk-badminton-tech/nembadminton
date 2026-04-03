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
 * Test the three validation rules for holdrunde squads:
 *
 *   1. "Fuldendt hold"                    — all categories filled with correct genders
 *   2. "Spiller for højt i kategorien"    — within-squad ranking order (§38 stk. 2+3)
 *   3. "Spiller på et forkert hold"       — cross-squad ranking order (§38 stk. 4)
 *
 * Each test creates a holdrunde with 3x 13-kamps hold and fills players to
 * trigger exactly one validation error. Adjust getSquadSlots*() to change
 * which players trigger which rule.
 *
 * Uses Juli 2025 rangliste with play date 14-07-2025 (same as the construct tests).
 */
class TeamFightValidationRulesTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    // ─── Shared setup ───────────────────────────────────────────────────

    /**
     * Login, create a holdrunde, and add 3x 13-kamps hold.
     *
     * Returns the browser on the edit page with TeamFightEditPage macros
     * registered and all 3 empty squads visible.
     */
    private function createHoldrundeWith3Squads(Browser $browser, string $name): void
    {
        $clubhouse = Clubhouse::first();

        // Login
        $browser->visit(new LoginPage())
            ->loginSPA('testing@gmail.com', 'Test1234');

        // Create holdrunde
        $createPage = new TeamFightCreatePage($clubhouse->id);
        $browser->visit($createPage)
            ->on($createPage);

        $browser->waitUntilEnabled('@name-input')
            ->type('@name-input', $name);

        $browser->on($createPage)
            ->selectDate(7, 2025, 14)
            ->selectRankingByText('Juli 2025');

        $browser->click('@submit-button')
            ->waitForText('Dit hold er gemt')
            ->assertPathContains('/team-fight/')
            ->assertPathContains('/edit')
            ->waitForText('Holdene i holdrunden');

        // Register edit page macros
        $browser->on(new TeamFightEditPage());

        // Add 3x 13-kamps hold
        for ($i = 0; $i < 3; $i++) {
            $browser->add13KampsHold();
            $browser->waitForText('Hold ' . ($i + 1));
        }
    }

    /**
     * Fill all 3 squads from the given slot arrays.
     */
    private function fillAllSquads(Browser $browser, array $allSquadSlots): void
    {
        for ($squadIdx = 0; $squadIdx < 3; $squadIdx++) {
            $browser->fillSquad($squadIdx, $allSquadSlots[$squadIdx]);
        }
    }

    // ─── Player assignments ─────────────────────────────────────────────
    //
    // Each getSquadSlots*() returns 3 arrays (one per squad), mapping
    // category name → [player1, player2, ...].
    //
    // Gender rules per category:
    //   MD = [woman, man]  DS = [woman]  HS = [man]
    //   DD = [woman, woman]  HD = [man, man]
    //
    // Points comments show ranking values relevant to the test.
    // Edit the player names below to adjust which rule gets triggered.
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Squad slots for test_fuldendt_hold: Squad 3 is left incomplete.
     *
     * Squads 1 and 2 are fully valid. Squad 3 has 4. HS left empty
     * so the "Fuldendt hold" check fires Fejl.
     */
    private function getSquadSlotsIncomplete(): array
    {
        return [
            // ── Squad 1 (Hold 1) — fully valid ──
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

            // ── Squad 2 (Hold 2) — fully valid ──
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

            // ── Squad 3 (Hold 3) — INCOMPLETE: 4. HS left empty ──
            // Lau Viskum Nielsen is removed from 4.HS and 2.HD to keep
            // the player count consistent (he doesn't appear at all).
            [
                '1. MD' => ['Emma Sejersen', 'Simon Dømmestrup'],
                '2. MD' => ['Hanne Juul Christensen', 'Per Christophersen'],
                '1. DS' => ['Amanda M. Sommer'],
                '2. DS' => ['Katrine Tingleff'],
                '1. HS' => ['Mathias Juul Hornbøll'],
                '2. HS' => ['Nikolaj Zawadzki'],
                '3. HS' => ['Frederik Balmer Odgaard'],
                // '4. HS' intentionally left empty
                '1. DD' => ['Emma Sejersen', 'Hanne Juul Christensen'],
                '2. DD' => ['Amanda M. Sommer', 'Katrine Tingleff'],
                '1. HD' => ['Simon Dømmestrup', 'Per Christophersen'],
                '2. HD' => ['Nikolaj Zawadzki', 'Frederik Balmer Odgaard'],
                '3. HD' => ['Mathias Juul Hornbøll'],
            ],
        ];
    }

    /**
     * Squad slots for test_spiller_for_hojt_i_kategorien: players within
     * a squad are ordered wrong so the lower-ranked player sits higher.
     *
     * All squads are fully filled (basic validation passes).
     *
     * ┌──────────────────────────────────────────────────────────────────┐
     * │ TO TRIGGER "Spiller for højt i kategorien":                     │
     * │ Swap two HS players within squad 1 so the player with fewer     │
     * │ points sits in a higher position (e.g., swap 1.HS and 4.HS).   │
     * │ The threshold is 50 points for singles.                         │
     * └──────────────────────────────────────────────────────────────────┘
     */
    private function getSquadSlotsCategoryViolation(): array
    {
        return [
            // ── Squad 1 (Hold 1) — CATEGORY VIOLATION ──
            // Nikolai Andersson (HS:3138) is placed at 1.HS
            // Kaj Lü (HS:3403) is placed at 4.HS
            // Difference: 3403 - 3138 = 265 > 50 → violation
            [
                '1. MD' => ['Josefine Eggert Jackson', 'Patrick Buhl'],
                '2. MD' => ['Sarah Berthelsen', 'Frederik Weibøl'],
                '1. DS' => ['Spela Silvester Laumand'],
                '2. DS' => ['Karoline Keller Rolsted'],
                '1. HS' => ['Nikolai Andersson'],     // swapped — lower points (3138)
                '2. HS' => ['Magnus Schøtt Jensen'],
                '3. HS' => ['Mads Sloth'],
                '4. HS' => ['Kaj Lü'],                // swapped — higher points (3403)
                '1. DD' => ['Josefine Eggert Jackson', 'Sarah Berthelsen'],
                '2. DD' => ['Spela Silvester Laumand', 'Karoline Keller Rolsted'],
                '1. HD' => ['Patrick Buhl', 'Frederik Weibøl'],
                '2. HD' => ['Kaj Lü', 'Magnus Schøtt Jensen'],
                '3. HD' => ['Mads Sloth', 'Nikolai Andersson'],
            ],

            // ── Squad 2 (Hold 2) — valid ──
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

            // ── Squad 3 (Hold 3) — valid ──
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

    /**
     * Squad slots for test_spiller_paa_forkert_hold: a strong player from
     * squad 1 is swapped with a weaker player from squad 3 so the lower
     * squad has a player that's too strong.
     *
     * All squads are fully filled (basic validation passes).
     *
     * ┌──────────────────────────────────────────────────────────────────┐
     * │ TO TRIGGER "Spiller på et forkert hold":                        │
     * │ Swap a strong squad-1 player with a weak squad-3 player.        │
     * │ The cross-squad threshold is 50 points. The strong player on    │
     * │ the lower squad must exceed the weak player on the higher squad │
     * │ by >50 points in ALL categories where the higher player plays.  │
     * └──────────────────────────────────────────────────────────────────┘
     */
    private function getSquadSlotsLevelViolation(): array
    {
        return [
            // ── Squad 1 (Hold 1) — has a weak player swapped in ──
            // Mathias Juul Hornbøll (HS:2858) swapped in from squad 3
            // (originally Kaj Lü HS:3403 was here)
            [
                '1. MD' => ['Josefine Eggert Jackson', 'Patrick Buhl'],
                '2. MD' => ['Sarah Berthelsen', 'Frederik Weibøl'],
                '1. DS' => ['Spela Silvester Laumand'],
                '2. DS' => ['Karoline Keller Rolsted'],
                '1. HS' => ['Mathias Juul Hornbøll'],  // swapped from squad 3 (weaker)
                '2. HS' => ['Magnus Schøtt Jensen'],
                '3. HS' => ['Mads Sloth'],
                '4. HS' => ['Nikolai Andersson'],
                '1. DD' => ['Josefine Eggert Jackson', 'Sarah Berthelsen'],
                '2. DD' => ['Spela Silvester Laumand', 'Karoline Keller Rolsted'],
                '1. HD' => ['Patrick Buhl', 'Frederik Weibøl'],
                '2. HD' => ['Mathias Juul Hornbøll', 'Magnus Schøtt Jensen'],
                '3. HD' => ['Mads Sloth', 'Nikolai Andersson'],
            ],

            // ── Squad 2 (Hold 2) — valid ──
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

            // ── Squad 3 (Hold 3) — has a strong player swapped in ──
            // Kaj Lü (HS:3403) swapped in from squad 1 (stronger than
            // anyone on this squad — triggers cross-squad violation)
            [
                '1. MD' => ['Emma Sejersen', 'Simon Dømmestrup'],
                '2. MD' => ['Hanne Juul Christensen', 'Per Christophersen'],
                '1. DS' => ['Amanda M. Sommer'],
                '2. DS' => ['Katrine Tingleff'],
                '1. HS' => ['Kaj Lü'],                // swapped from squad 1 (stronger)
                '2. HS' => ['Nikolaj Zawadzki'],
                '3. HS' => ['Frederik Balmer Odgaard'],
                '4. HS' => ['Lau Viskum Nielsen'],
                '1. DD' => ['Emma Sejersen', 'Hanne Juul Christensen'],
                '2. DD' => ['Amanda M. Sommer', 'Katrine Tingleff'],
                '1. HD' => ['Simon Dømmestrup', 'Per Christophersen'],
                '2. HD' => ['Kaj Lü', 'Lau Viskum Nielsen'],
                '3. HD' => ['Frederik Balmer Odgaard', 'Nikolaj Zawadzki'],
            ],
        ];
    }

    // ─── Test methods ───────────────────────────────────────────────────

    /**
     * Test: "Fuldendt hold" shows "Fejl" when a squad has an empty category.
     *
     * Squad 3 has 4. HS left empty. The other two validations are gated
     * and should show "-" (deactivated).
     */
    public function test_fuldendt_hold_shows_fejl_when_squad_is_incomplete(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createHoldrundeWith3Squads($browser, 'Validation: Fuldendt hold');

            $this->fillAllSquads($browser, $this->getSquadSlotsIncomplete());

            $browser->assertIncompleteTeamFailing();

            $browser->screenshot('validation-fuldendt-hold-fejl');
        });
    }

    /**
     * Test: "Spiller for højt i kategorien" shows "Fejl" when a player within
     * a squad is ranked too high for their position (§38 stk. 2+3).
     *
     * Squad 1 has HS players in wrong order — the weaker player sits higher.
     * Basic validation ("Fuldendt hold") should pass since all squads are complete.
     */
    public function test_spiller_for_hojt_i_kategorien_shows_fejl(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createHoldrundeWith3Squads($browser, 'Validation: Kategori');

            $this->fillAllSquads($browser, $this->getSquadSlotsCategoryViolation());

            $browser->assertAllSlotsFilled();
            $browser->assertCategoryValidationFailing();

            $browser->screenshot('validation-kategori-fejl');
        });
    }

    /**
     * Test: "Spiller på et forkert hold" shows "Fejl" when a player on a lower
     * squad is too strong compared to players on higher squads (§38 stk. 4).
     *
     * A strong player from squad 1 (Kaj Lü) is placed on squad 3, while
     * a weaker player (Mathias Juul Hornbøll) takes his spot on squad 1.
     * Basic validation ("Fuldendt hold") should pass since all squads are complete.
     */
    public function test_spiller_paa_forkert_hold_shows_fejl(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createHoldrundeWith3Squads($browser, 'Validation: Forkert hold');

            $this->fillAllSquads($browser, $this->getSquadSlotsLevelViolation());

            $browser->assertAllSlotsFilled();
            $browser->assertLevelValidationFailing();

            $browser->screenshot('validation-forkert-hold-fejl');
        });
    }
}
