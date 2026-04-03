<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;

class TeamFightEditPage extends Page
{
    private ?int $clubhouseId;
    private ?string $teamUUID;

    /**
     * Constructor accepts optional args so the page can be instantiated
     * just to register macros (via ->on()) when already on the edit page
     * after a redirect — without knowing the clubhouseId/teamUUID upfront.
     */
    public function __construct(?int $clubhouseId = null, ?string $teamUUID = null)
    {
        $this->clubhouseId = $clubhouseId;
        $this->teamUUID = $teamUUID;
    }

    public function url(): string
    {
        if ($this->clubhouseId && $this->teamUUID) {
            return '/app/c-' . $this->clubhouseId . '/team-fight/' . $this->teamUUID . '/edit';
        }

        // When no args provided, return empty string — page is used for macro registration only
        return '';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page');
    }

    public function elements(): array
    {
        return [
            '@page' => "[dusk='team-fight-edit-page']",
            '@player-search-panel' => "[dusk='player-search-panel']",
            '@player-search-input' => "[dusk='player-search-input'] input",
            '@ranking-list-select' => "[dusk='ranking-list-select'] select",
            '@player-search-table' => "[dusk='player-search-table']",
            '@team-table-section' => "[dusk='team-table-section']",
            '@add-teams-section' => "[dusk='add-teams-section']",
            '@add-13-kamps-hold-button' => "[dusk='add-13-kamps-hold-button']",
            '@validation-incomplete-team' => "[dusk='validation-incomplete-team']",
            '@validation-invalid-level' => "[dusk='validation-invalid-level']",
            '@validation-invalid-category' => "[dusk='validation-invalid-category']",
        ];
    }

    // ─── Ranking list panel methods ──────────────────────────────────────

    /**
     * Switch the ranking list category filter in the player search panel.
     *
     * The Buefy b-select wrapper requires JS value assignment + event dispatch.
     * Valid values: WOMEN_MIX, MEN_MIX, WOMEN_SINGLE, MEN_SINGLE,
     *               WOMEN_DOUBLE, MEN_DOUBLE, WOMEN_MIX_DOUBLE, MEN_MIX_DOUBLE.
     */
    public function switchRankingList(Browser $browser, string $value): void
    {
        $browser->script("
            var sel = document.querySelector(\"[dusk='ranking-list-select']\");
            if (sel.tagName !== 'SELECT') sel = sel.querySelector('select') || sel;
            sel.value = '{$value}';
            sel.dispatchEvent(new Event('input'));
            sel.dispatchEvent(new Event('change'));
        ");
        $browser->waitFor("[dusk='player-search-panel'] table tbody tr", 15);
    }

    /**
     * Add the first N players from the ranking list by clicking the + button.
     *
     * Each click triggers a GraphQL mutation that adds the player to the squad.
     * We wait for the "Tilføjet til Hold" snackbar confirmation between clicks.
     */
    public function addPlayersFromRankingList(Browser $browser, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $browser->waitFor("[dusk='player-search-panel'] table tbody tr", 10);

            $browser->script("
                var buttons = document.querySelectorAll(\"[dusk='player-search-panel'] table tbody tr [dusk='add-player-button']\");
                if (buttons.length > 0) buttons[0].click();
            ");

            $browser->waitForText('Tilføjet til Hold', 10)
                ->pause(500);
        }
    }

    // ─── Squad / team table methods ─────────────────────────────────────

    /**
     * Click the 13-kamps hold button to add a squad.
     *
     * Uses JS click to avoid Buefy snackbar toast intercepting the button.
     */
    public function add13KampsHold(Browser $browser): void
    {
        $browser->waitFor('@add-13-kamps-hold-button')
            ->scrollTo('@add-13-kamps-hold-button');
        $browser->script("document.querySelector(\"[dusk='add-13-kamps-hold-button']\").click()");
    }

    /**
     * Fill the next empty inline autocomplete slot in the team table.
     *
     * Finds the first <input placeholder="Søg på spiller..."> in the team table,
     * tags it with a temporary ID, uses Dusk ->keys() for real keyboard events
     * (a space character triggers the squad member search), then clicks the first
     * dropdown result.
     *
     * Used by the single-team test to auto-fill DD/HD categories with existing
     * squad members without specifying exact player names.
     */
    public function fillNextInlineSlot(Browser $browser): void
    {
        $tempId = 'dusk-next-slot-' . md5(microtime());

        // Dismiss any open autocomplete dropdown from a previous fill,
        // then tag the first empty input with a temporary ID and scroll to it.
        $found = $browser->script("
            // Close any lingering dropdown by clicking the document body
            document.body.click();
            var inputs = document.querySelectorAll(\"[dusk='team-table-section'] input[placeholder='Søg på spiller...']\");
            if (inputs.length > 0) {
                inputs[0].id = '{$tempId}';
                inputs[0].scrollIntoView({block: 'center'});
                return true;
            }
            return false;
        ");

        Assert::assertTrue($found[0] ?? false, 'No empty inline slot found');

        // Wait for scroll to settle, then use real keyboard events.
        // A space character triggers the Buefy @typing handler to list all squad members.
        $selector = "#{$tempId}";
        $browser->pause(300);
        $browser->click($selector);
        $browser->pause(200);
        $browser->keys($selector, ' ');

        // Wait for dropdown results to appear
        $browser->waitFor('.autocomplete .dropdown-menu .dropdown-content .dropdown-item', 10);

        // Click the first dropdown result
        $browser->script("
            var dropdown = document.querySelector('.autocomplete .dropdown-menu .dropdown-content .dropdown-item');
            if (dropdown) dropdown.click();
        ");
        $browser->pause(600);

        // Clean up temporary ID
        $browser->script("
            var el = document.getElementById('{$tempId}');
            if (el) el.removeAttribute('id');
        ");
    }

    /**
     * Fill an inline autocomplete slot within a specific squad and category.
     *
     * Targets the empty <input placeholder="Søg på spiller..."> inside the row
     * whose <th> matches the given category name within the given squad's table.
     *
     * Approach:
     *   1. Tag the target input with a temporary unique ID via JS so Dusk can
     *      address it with ->keys() for real keyboard input
     *   2. Use Dusk ->click() + ->keys() to type the search — this fires real
     *      keyboard events that Buefy's @typing handler picks up
     *   3. Poll until the specific player name appears in the dropdown
     *      (the autocomplete has a 300ms debounce + network round-trip)
     *   4. Click the dropdown item matching the player name
     *
     * @param int    $squadIndex   0-based squad index (0 = Hold 1, 1 = Hold 2, etc.)
     * @param string $categoryName Category label as shown in the <th>, e.g. "1. DD"
     * @param string $playerName   Full player name to search for
     */
    public function fillCategorySlot(Browser $browser, int $squadIndex, string $categoryName, string $playerName): void
    {
        $escapedCategory = addslashes($categoryName);
        $escapedPlayer = addslashes($playerName);

        // Unique temporary ID so Dusk can target this specific input
        $tempId = 'dusk-fill-target-' . $squadIndex . '-' . md5($categoryName . $playerName . microtime());

        // Step 1: Dismiss any lingering dropdown from a previous fill, then find
        // the empty input in the correct squad table + category row.
        // Each squad is a <table> inside [dusk='team-table-section']. Rows have
        // <th> with category name and <td> with PlayerSearch inputs when empty.
        $found = $browser->script("
            // Close any open autocomplete dropdown first
            document.body.click();
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

        Assert::assertTrue($found[0] ?? false, "Could not find empty input for squad {$squadIndex}, category '{$categoryName}'");

        // Wait for scroll to settle before interacting (CI Chrome is slower)
        $selector = "#{$tempId}";
        $browser->pause(300);

        // Step 2: Click to focus, then type the player name with real keystrokes.
        $browser->click($selector);
        $browser->pause(200);
        $browser->keys($selector, $playerName);

        // Step 3: Wait until the specific player name appears in the dropdown.
        // Must NOT just wait for any .dropdown-item — that matches stale/loading results.
        // Uses 15s timeout for CI where GraphQL responses can be slower.
        $browser->waitUsing(15, 200, function () use ($browser, $escapedPlayer) {
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
        }, "Timed out waiting for '{$playerName}' in autocomplete dropdown (squad {$squadIndex}, {$categoryName})");

        // Step 4: Click the dropdown item matching the player name.
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

        // Clean up temporary ID
        $browser->script("
            var el = document.getElementById('{$tempId}');
            if (el) el.removeAttribute('id');
        ");
    }

    /**
     * Fill all category slots for one squad using the category → players mapping.
     *
     * @param int   $squadIndex    0-based squad index
     * @param array $categorySlots Map of category name → [player1, player2, ...]
     */
    public function fillSquad(Browser $browser, int $squadIndex, array $categorySlots): void
    {
        foreach ($categorySlots as $categoryName => $players) {
            foreach ($players as $playerName) {
                $this->fillCategorySlot($browser, $squadIndex, $categoryName, $playerName);
            }
        }
    }

    // ─── Assertion methods ──────────────────────────────────────────────

    /**
     * Assert all player slots in the team table are filled (no empty autocomplete inputs).
     */
    public function assertAllSlotsFilled(Browser $browser): void
    {
        $emptyInputs = $browser->script("
            return document.querySelectorAll(\"[dusk='team-table-section'] input[placeholder='Søg på spiller...']\").length;
        ");
        Assert::assertEquals(0, $emptyInputs[0], 'All player slots should be filled');
    }

    /**
     * Assert the three validation status indicators are not showing errors.
     *
     * Checks:
     *   - "Fuldendt hold" shows "OK"
     *   - "Spiller på et forkert hold" does not show "Fejl"
     *   - "Spiller for højt i kategorien" does not show "Fejl"
     */
    public function assertValidationPassing(Browser $browser): void
    {
        // Wait for the validation section to appear, then scroll it into view via JS
        // (Dusk's scrollTo throws if the element isn't rendered yet)
        $browser->waitFor('@validation-incomplete-team', 10);
        $browser->script("
            var el = document.querySelector(\"[dusk='validation-incomplete-team']\");
            if (el) el.scrollIntoView({block: 'center'});
        ");
        $browser->pause(500)
            ->assertSeeIn('@validation-incomplete-team', 'OK')
            ->assertDontSeeIn('@validation-invalid-level', 'Fejl')
            ->assertDontSeeIn('@validation-invalid-category', 'Fejl');
    }
}
