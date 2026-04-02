<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
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

    /**
     * Fill an inline autocomplete slot within a specific squad and category.
     *
     * Finds the empty <input placeholder="Søg på spiller..."> inside the row
     * whose <th> matches the given category name (e.g. "1. DD") within the
     * given squad's table (by squad index).
     *
     * Approach:
     *   1. Tag the target input with a temporary unique ID via JS so Dusk can
     *      address it with ->keys() for real keyboard input
     *   2. Use Dusk ->click() + ->keys() to type the search term — this fires
     *      real keyboard events that Buefy's @typing handler picks up
     *      (the native value setter approach does NOT trigger Buefy events)
     *   3. Wait for the autocomplete dropdown to appear
     *   4. Click the dropdown item whose text contains the player's name
     *      (not just the first item — avoids picking the wrong player)
     *
     * @param int    $squadIndex   0-based squad index (0 = Hold 1, 1 = Hold 2, etc.)
     * @param string $categoryName Category label as shown in the <th>, e.g. "1. DD", "2. HD"
     * @param string $playerName   Full player name to search for in the autocomplete
     */
    private function fillCategorySlot(Browser $browser, int $squadIndex, string $categoryName, string $playerName): void
    {
        $escapedCategory = addslashes($categoryName);
        $escapedPlayer = addslashes($playerName);

        // Use a unique temporary ID to let Dusk target the correct input.
        // We tag the input via JS, then use a CSS selector to click/type into it.
        $tempId = 'dusk-fill-target-' . $squadIndex . '-' . md5($categoryName . $playerName . microtime());

        // Step 1: Find the empty input inside the correct squad table + category row.
        //
        // DOM structure: each squad is a <table> inside [dusk='team-table-section'].
        // Within each table, rows have <th> with the category name (e.g. "1. DD")
        // and <td> with PlayerSearch <input placeholder="Søg på spiller..."> when
        // the slot is empty. We scroll to it and assign a temporary ID.
        $found = $browser->script("
            var tables = document.querySelectorAll(\"[dusk='team-table-section'] table.table\");
            var table = tables[{$squadIndex}];
            if (!table) return false;
            var rows = table.querySelectorAll('tbody tr');
            for (var i = 0; i < rows.length; i++) {
                var th = rows[i].querySelector('th');
                if (th && th.textContent.trim() === '{$escapedCategory}') {
                    var input = rows[i].querySelector('input[placeholder=\"Søg på spiller...\"]');
                    if (input) {
                        input.id = '{$tempId}';
                        input.scrollIntoView({block: 'center'});
                        return true;
                    }
                    return false;
                }
            }
            return false;
        ");

        $this->assertTrue($found[0] ?? false, "Could not find empty input for squad {$squadIndex}, category '{$categoryName}'");

        // Step 2: Click to focus the input, then type the player name using real
        // keyboard events. Buefy's b-autocomplete listens for @typing (keyboard input)
        // to trigger the search — DOM 'input' events from native value setter don't work.
        //
        // The GraphQL query does a LIKE '%name%' match, so the full name works.
        $selector = "#{$tempId}";
        $browser->click($selector);
        $browser->pause(200);

        // Type using Dusk's keys() which sends real keystrokes.
        $browser->keys($selector, $playerName);

        // Step 3: Wait until the specific player name appears in a dropdown item.
        //
        // The autocomplete fires a debounced GraphQL search (300ms debounce + network).
        // We must NOT just wait for any .dropdown-item — that could match stale/loading
        // results from a previous search. Instead, poll until a dropdown item's text
        // contains the exact player name we searched for.
        $browser->waitUsing(10, 200, function () use ($browser, $escapedPlayer) {
            $found = $browser->script("
                var items = document.querySelectorAll('.autocomplete .dropdown-menu .dropdown-content .dropdown-item');
                for (var i = 0; i < items.length; i++) {
                    if (items[i].textContent.indexOf('{$escapedPlayer}') !== -1) {
                        return true;
                    }
                }
                return false;
            ");
            return $found[0] ?? false;
        }, "Timed out waiting for '{$playerName}' to appear in autocomplete dropdown (squad {$squadIndex}, {$categoryName})");

        // Step 4: Click the dropdown item that contains the exact player name.
        $browser->script("
            var items = document.querySelectorAll('.autocomplete .dropdown-menu .dropdown-content .dropdown-item');
            for (var i = 0; i < items.length; i++) {
                if (items[i].textContent.indexOf('{$escapedPlayer}') !== -1) {
                    items[i].click();
                    return;
                }
            }
        ");
        $browser->pause(600);

        // Clean up: remove the temporary ID to avoid DOM pollution
        $browser->script("
            var el = document.getElementById('{$tempId}');
            if (el) el.removeAttribute('id');
        ");
    }

    /**
     * Fill all category slots for one squad using the category → players mapping.
     *
     * Iterates through all 13 categories in order and fills each player into
     * the correct category row via inline autocomplete.
     *
     * @param int   $squadIndex    0-based squad index
     * @param array $categorySlots Map of category name → [player1, player2, ...]
     */
    private function fillSquad(Browser $browser, int $squadIndex, array $categorySlots): void
    {
        foreach ($categorySlots as $categoryName => $players) {
            foreach ($players as $playerName) {
                $this->fillCategorySlot($browser, $squadIndex, $categoryName, $playerName);
            }
        }
    }

    public function test_user_can_construct_holdrunde_with_3x_13_kamps_hold(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            // ── Step 1: Login ──
            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234');

            // ── Step 2: Create holdrunde ──
            $browser->visit(new TeamFightCreatePage($clubhouse->id))
                ->on(new TeamFightCreatePage($clubhouse->id));

            $browser->waitUntilEnabled('@name-input')
                ->type('@name-input', '3x13 Kamps Holdrunde Test');

            // Select date: July 14, 2025 via Buefy datepicker.
            // Buefy datepicker month/year selects need JS value changes with
            // event dispatching — Dusk's native select() doesn't work here.
            $browser->click('@date-picker')
                ->waitFor('.datepicker .dropdown-content');

            // Set month to July (value=6 because months are 0-indexed)
            $browser->script("
                var monthSel = document.querySelector('.datepicker .datepicker-header .select select');
                monthSel.value = '6';
                monthSel.dispatchEvent(new Event('input'));
                monthSel.dispatchEvent(new Event('change'));
            ");
            // Set year to 2025 (second <select> in the datepicker header)
            $browser->script("
                var yearSel = document.querySelectorAll('.datepicker .datepicker-header .select select')[1];
                yearSel.value = '2025';
                yearSel.dispatchEvent(new Event('input'));
                yearSel.dispatchEvent(new Event('change'));
            ");
            $browser->pause(300);

            // Click day 14, skipping "is-nearby" cells (days from adjacent months)
            $browser->script("
                var cells = document.querySelectorAll('.datepicker .datepicker-body a.datepicker-cell');
                for (var i = 0; i < cells.length; i++) {
                    if (cells[i].textContent.trim() === '14' && !cells[i].classList.contains('is-nearby')) {
                        cells[i].click();
                        break;
                    }
                }
            ");
            $browser->pause(300);

            // Select Juli 2025 ranking from the ranking dropdown.
            // Loops through <option> elements to find the one containing "Juli 2025".
            $browser->waitFor('@ranking-select')
                ->script("
                    var sel = document.querySelector(\"[dusk='team-fight-ranking-select'] select\");
                    var options = sel.options;
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].text.indexOf('Juli 2025') !== -1) {
                            sel.selectedIndex = i;
                            sel.dispatchEvent(new Event('input'));
                            sel.dispatchEvent(new Event('change'));
                            break;
                        }
                    }
                ");

            // Submit the form and wait for redirect to the edit page
            $browser->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->assertPathContains('/team-fight/')
                ->assertPathContains('/edit')
                ->waitForText('Holdene i holdrunden')
                ->waitForText('3x13 Kamps Holdrunde Test');

            // ── Step 3: Add 3x 13-kamps hold ──
            // JS click to avoid Buefy snackbar toast intercepting the button
            for ($i = 0; $i < 3; $i++) {
                $browser->waitFor("[dusk='add-13-kamps-hold-button']")
                    ->scrollTo("[dusk='add-13-kamps-hold-button']");
                $browser->script("document.querySelector(\"[dusk='add-13-kamps-hold-button']\").click()");
                $browser->waitForText('Hold ' . ($i + 1));
            }

            // Verify all 3 squads exist
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
            // Each squad must be fully filled before moving to the next, because
            // the autocomplete search within a category only returns players who
            // are already members of that squad's team fight.
            //
            // To change who plays where, edit getSquadSlots().
            // =====================================================================

            $allSquadSlots = $this->getSquadSlots();

            for ($squadIdx = 0; $squadIdx < 3; $squadIdx++) {
                $squadNum = $squadIdx + 1;
                $browser->screenshot("3x13kamps-squad-{$squadNum}-before");

                $this->fillSquad($browser, $squadIdx, $allSquadSlots[$squadIdx]);

                $browser->screenshot("3x13kamps-squad-{$squadNum}-filled");
            }

            // ── Step 5: Verify all squads are still visible and fully populated ──
            for ($i = 1; $i <= 3; $i++) {
                $browser->assertSee("Hold {$i}");
            }

            // Count remaining empty autocomplete inputs — should be zero
            $emptyInputs = $browser->script("
                return document.querySelectorAll(\"[dusk='team-table-section'] input[placeholder='Søg på spiller...']\").length;
            ");
            $this->assertEquals(0, $emptyInputs[0], 'All player slots should be filled');

            // ── Step 6: Verify each player ended up in the correct category ──
            //
            // Reads the actual player names from each squad's table in the DOM
            // and compares them against our hardcoded getSquadSlots() mapping.
            //
            // JS returns a nested structure: squads → categories → player names.
            // Each category row's <th> gives the category name (e.g. "1. DD"),
            // and the <p class="handle"> elements inside that row contain the
            // player names (with gender icon text and position info stripped).
            $actualSquads = $browser->script("
                var tables = document.querySelectorAll(\"[dusk='team-table-section'] table.table\");
                var result = [];
                for (var t = 0; t < tables.length; t++) {
                    var squad = {};
                    var rows = tables[t].querySelectorAll('tbody tr');
                    for (var r = 0; r < rows.length; r++) {
                        var th = rows[r].querySelector('th');
                        if (!th) continue;
                        var categoryName = th.textContent.trim();
                        var handles = rows[r].querySelectorAll('p.handle');
                        var players = [];
                        for (var h = 0; h < handles.length; h++) {
                            // The handle contains: icon text + player name + ' (position info)'
                            // Extract just the player name by removing the trailing parenthetical
                            var text = handles[h].textContent.trim();
                            var match = text.match(/^(.+?)\\s*\\(/);
                            if (match) {
                                players.push(match[1].trim());
                            } else {
                                players.push(text);
                            }
                        }
                        squad[categoryName] = players;
                    }
                    result.push(squad);
                }
                return result;
            ");

            $actualSquadData = $actualSquads[0];

            for ($squadIdx = 0; $squadIdx < 3; $squadIdx++) {
                $squadNum = $squadIdx + 1;
                $expectedCategories = $allSquadSlots[$squadIdx];
                $actualCategories = $actualSquadData[$squadIdx];

                foreach ($expectedCategories as $categoryName => $expectedPlayers) {
                    $this->assertArrayHasKey(
                        $categoryName,
                        $actualCategories,
                        "Squad {$squadNum}: category '{$categoryName}' not found in DOM"
                    );

                    $actualPlayers = $actualCategories[$categoryName];

                    $this->assertCount(
                        count($expectedPlayers),
                        $actualPlayers,
                        "Squad {$squadNum}, {$categoryName}: expected " . count($expectedPlayers)
                            . " player(s) but found " . count($actualPlayers)
                    );

                    foreach ($expectedPlayers as $i => $expectedName) {
                        $this->assertEquals(
                            $expectedName,
                            $actualPlayers[$i],
                            "Squad {$squadNum}, {$categoryName}, slot {$i}: expected '{$expectedName}' but got '{$actualPlayers[$i]}'"
                        );
                    }
                }
            }

            // ── Step 7: Verify validation status indicators ──
            // After all categories are filled, the validation checks should run.
            // Each indicator is a .tags.has-addons with a label span and a status span.
            // Status: is-success + "OK" = pass, is-danger + "Fejl" = fail, is-light + "-" = disabled
            $browser->scrollTo('.field.is-grouped')
                ->pause(500);

            // Assert "Fuldendt hold" shows OK (not Fejl)
            $fuldendtStatus = $browser->script("
                var spans = document.querySelectorAll('.tags.has-addons');
                for (var i = 0; i < spans.length; i++) {
                    if (spans[i].textContent.includes('Fuldendt hold')) {
                        var statusSpan = spans[i].querySelector('.tag.is-medium:not(.is-light)') || spans[i].querySelectorAll('.tag')[1];
                        return { text: statusSpan.textContent.trim(), isDanger: statusSpan.classList.contains('is-danger'), isSuccess: statusSpan.classList.contains('is-success') };
                    }
                }
                return null;
            ");
            $this->assertNotNull($fuldendtStatus[0], '"Fuldendt hold" indicator should be present');
            $this->assertEquals('OK', $fuldendtStatus[0]['text'], '"Fuldendt hold" should show OK');
            $this->assertTrue($fuldendtStatus[0]['isSuccess'], '"Fuldendt hold" should have is-success class');
            $this->assertFalse($fuldendtStatus[0]['isDanger'], '"Fuldendt hold" should not have is-danger class');

            // Assert "Spiller på et forkert hold" shows OK (not Fejl)
            $forkertHoldStatus = $browser->script("
                var spans = document.querySelectorAll('.tags.has-addons');
                for (var i = 0; i < spans.length; i++) {
                    if (spans[i].textContent.includes('Spiller på et forkert hold')) {
                        var statusSpan = spans[i].querySelectorAll('.tag')[1];
                        return { text: statusSpan.textContent.trim(), isDanger: statusSpan.classList.contains('is-danger'), isSuccess: statusSpan.classList.contains('is-success') };
                    }
                }
                return null;
            ");
            $this->assertNotNull($forkertHoldStatus[0], '"Spiller på et forkert hold" indicator should be present');
            $this->assertNotEquals('Fejl', $forkertHoldStatus[0]['text'], '"Spiller på et forkert hold" should not show Fejl');
            $this->assertFalse($forkertHoldStatus[0]['isDanger'], '"Spiller på et forkert hold" should not have is-danger class');

            // Assert "Spiller for højt i kategorien" shows OK (not Fejl)
            $forHoejtStatus = $browser->script("
                var spans = document.querySelectorAll('.tags.has-addons');
                for (var i = 0; i < spans.length; i++) {
                    if (spans[i].textContent.includes('Spiller for højt i kategorien')) {
                        var statusSpan = spans[i].querySelectorAll('.tag')[1];
                        return { text: statusSpan.textContent.trim(), isDanger: statusSpan.classList.contains('is-danger'), isSuccess: statusSpan.classList.contains('is-success') };
                    }
                }
                return null;
            ");
            $this->assertNotNull($forHoejtStatus[0], '"Spiller for højt i kategorien" indicator should be present');
            $this->assertNotEquals('Fejl', $forHoejtStatus[0]['text'], '"Spiller for højt i kategorien" should not show Fejl');
            $this->assertFalse($forHoejtStatus[0]['isDanger'], '"Spiller for højt i kategorien" should not have is-danger class');

            $browser->screenshot('3x13kamps-test-complete');
        });
    }
}
