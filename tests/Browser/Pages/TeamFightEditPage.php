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
     * scrolls to it, focuses it via Dusk click + types a space via Dusk keys
     * (real OS-level keyboard events that Buefy's @typing handler picks up),
     * then clicks the first dropdown result.
     *
     * Used by the single-team test to auto-fill DD/HD categories with existing
     * squad members without specifying exact player names.
     */
    public function fillNextInlineSlot(Browser $browser): void
    {
        // The CSS selector that always targets the first empty autocomplete input
        $selector = "[dusk='team-table-section'] input[placeholder='Søg på spiller...']";

        // Count empty slots before this fill so we can verify one was consumed
        $countBefore = $browser->script("
            return document.querySelectorAll(\"{$selector}\").length;
        ");
        $slotsBefore = $countBefore[0] ?? 0;
        Assert::assertGreaterThan(0, $slotsBefore, 'No empty inline slot found');

        // Close any lingering dropdown and scroll the first empty input into view
        $browser->script("
            document.body.click();
            var input = document.querySelector(\"{$selector}\");
            if (input) input.scrollIntoView({block: 'center'});
        ");
        $browser->pause(200);

        // Use Dusk click + keys for real keyboard events that Buefy reacts to.
        // A space character triggers the @typing handler to list all squad members.
        $browser->click($selector);
        $browser->keys($selector, ' ');

        // Wait for dropdown result to appear. If it doesn't, retry the focus+type
        // sequence — Vue may have re-rendered the input between click and keys.
        $browser->waitUsing(10, 300, function () use ($browser, $selector) {
            $hasDropdown = $browser->script("
                return document.querySelectorAll('.autocomplete .dropdown-menu .dropdown-content .dropdown-item').length > 0;
            ");
            if ($hasDropdown[0] ?? false) {
                return true;
            }
            // Retry: re-focus and re-type
            $browser->script("document.body.click();");
            $browser->pause(200);
            $browser->click($selector);
            $browser->keys($selector, ' ');
            return false;
        }, 'Could not open autocomplete dropdown for inline slot');

        // Click the first dropdown result via JS
        $browser->script("
            var item = document.querySelector('.autocomplete .dropdown-menu .dropdown-content .dropdown-item');
            if (item) item.click();
        ");

        // Wait until Vue processes the selection and the empty slot count decreases
        $browser->waitUsing(5, 200, function () use ($browser, $selector, $slotsBefore) {
            $countAfter = $browser->script("
                return document.querySelectorAll(\"{$selector}\").length;
            ");
            return ($countAfter[0] ?? $slotsBefore) < $slotsBefore;
        }, 'Slot was not consumed after clicking dropdown item');
    }

    /**
     * Fill an inline autocomplete slot within a specific squad and category.
     *
     * Uses dusk selectors for targeting:
     *   - [dusk='squad-{N}'] scopes to the correct squad
     *   - [dusk='player-search-autocomplete-{slug}'] scopes to the correct category
     *
     * Approach — all done via JS to avoid Dusk CSS selector lookups that break
     * when Vue re-renders destroy/recreate the input element after scrollIntoView:
     *
     *   1. Find the target input via dusk selectors, scroll, focus, and trigger
     *      a search via Vue internals (set focusedFlag + querySearchName)
     *   2. Poll (via waitUsing) until the player name appears in the dropdown
     *   3. Click the matching dropdown item via JS
     *   4. Verify the player was placed — retry the full sequence if not
     *
     * @param int    $squadIndex   0-based squad index (0 = Hold 1, 1 = Hold 2, etc.)
     * @param string $categoryName Category label as shown in the <th>, e.g. "1. DD"
     * @param string $playerName   Full player name to search for
     */
    public function fillCategorySlot(Browser $browser, int $squadIndex, string $categoryName, string $playerName): void
    {
        $escapedPlayer = addslashes($playerName);
        $categorySlug = self::slugifyCategory($categoryName);
        // Use only the first 5 chars of the name as the search query — enough
        // to narrow results, fast to dispatch, and avoids issues with special chars.
        $escapedSearch = addslashes(mb_substr($playerName, 0, 5));

        // CSS selector scoped to the correct squad + category via dusk attributes.
        // Buefy's <b-autocomplete> passes the dusk attribute down to the <input>
        // element directly, so no trailing " input" is needed.
        // Doubles categories (DD/HD/MD) can have two inputs — we always target
        // the first available one.
        $duskSelector = "[dusk='squad-{$squadIndex}'] [dusk='player-search-autocomplete-{$categorySlug}']";

        // JS snippet: find the input via dusk selector, trigger search via Vue
        // internals, and return true if the input was found.
        $triggerSearchJs = "
            // Dismiss any lingering dropdown from a previous fillCategorySlot call
            document.body.click();

            // Find first available input for this squad + category
            var input = document.querySelector(\"{$duskSelector}\");
            if (!input) return false;

            input.scrollIntoView({block: 'center'});
            input.focus();
            input.click();

            // Walk up from <input> to find PlayerSearch Vue instance and Buefy
            // autocomplete instance. PlayerSearch has 'querySearchName' (search
            // text) and 'focusedFlag' (gates Apollo queries). Buefy autocomplete
            // has 'isActive' (controls dropdown visibility) and 'newValue'.
            var query = '{$escapedSearch}';
            var el = input;
            var playerSearch = null;
            var autocomplete = null;
            while (el) {
                if (el.__vue__) {
                    if (el.__vue__.hasOwnProperty('querySearchName')) {
                        playerSearch = el.__vue__;
                    }
                    if (el.__vue__.hasOwnProperty('isActive') && typeof el.__vue__.onInput === 'function') {
                        autocomplete = el.__vue__;
                    }
                }
                el = el.parentElement;
            }
            // Set focusedFlag=true to ungate the Apollo search queries,
            // then set the search string to trigger a reactive refetch.
            if (playerSearch) {
                playerSearch.focusedFlag = true;
                playerSearch.querySearchName = query;
            }
            // Open dropdown and sync displayed value with the search query.
            if (autocomplete) {
                autocomplete.isActive = true;
                autocomplete.newValue = query;
                input.value = query;
            }
            return true;
        ";

        // JS snippet: check whether the player name appears in a <p class="handle">
        // inside the squad wrapper. Confirms the dropdown click actually worked.
        $verifyPlacedJs = "
            var squad = document.querySelector(\"[dusk='squad-{$squadIndex}']\");
            if (!squad) return false;
            var handles = squad.querySelectorAll('p.handle');
            for (var i = 0; i < handles.length; i++) {
                if (handles[i].textContent.indexOf('{$escapedPlayer}') !== -1) {
                    return true;
                }
            }
            return false;
        ";

        // Outer retry loop: attempts the full search → wait → click → verify
        // sequence up to 3 times. In CI, the dropdown click can silently fail
        // (dropdown closes between detection and click), so we must verify the
        // player was actually placed and retry the entire sequence if not.
        $maxAttempts = 3;
        $placed = false;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // Step 1: Trigger the search
            $found = $browser->script($triggerSearchJs);
            Assert::assertTrue(
                $found[0] ?? false,
                "Could not find empty input for squad {$squadIndex}, category '{$categoryName}' (attempt {$attempt})"
            );

            // Step 2: Wait for the player name to appear in the dropdown.
            // Re-trigger the search every ~900ms if the dropdown isn't populated.
            $retryCount = 0;
            try {
                $browser->waitUsing(15, 300, function () use ($browser, $escapedPlayer, $triggerSearchJs, &$retryCount) {
                    $found = $browser->script("
                        var items = document.querySelectorAll('.autocomplete .dropdown-menu .dropdown-content .dropdown-item');
                        for (var i = 0; i < items.length; i++) {
                            if (items[i].textContent.indexOf('{$escapedPlayer}') !== -1) {
                                return true;
                            }
                        }
                        return false;
                    ");
                    if ($found[0] ?? false) {
                        return true;
                    }
                    $retryCount++;
                    if ($retryCount % 3 === 0) {
                        $browser->script($triggerSearchJs);
                    }
                    return false;
                }, "dropdown-wait-attempt-{$attempt}");
            } catch (\Facebook\WebDriver\Exception\TimeoutException $e) {
                if ($attempt === $maxAttempts) {
                    Assert::fail("Timed out waiting for '{$playerName}' in autocomplete dropdown (squad {$squadIndex}, {$categoryName}) after {$maxAttempts} attempts");
                }
                continue;
            }

            // Step 3: Click the dropdown item matching the player name.
            $browser->script("
                var items = document.querySelectorAll('.autocomplete .dropdown-menu .dropdown-content .dropdown-item');
                for (var i = 0; i < items.length; i++) {
                    if (items[i].textContent.indexOf('{$escapedPlayer}') !== -1) {
                        items[i].click();
                        return;
                    }
                }
            ");

            // Step 4: Verify the player was actually placed — poll for up to 3s.
            try {
                $browser->waitUsing(3, 200, function () use ($browser, $verifyPlacedJs) {
                    $result = $browser->script($verifyPlacedJs);
                    return $result[0] ?? false;
                }, "verify-placed-attempt-{$attempt}");
                $placed = true;
                break;
            } catch (\Facebook\WebDriver\Exception\TimeoutException $e) {
                // Player was not placed — the click silently failed. If we have
                // remaining attempts, dismiss the stale dropdown and retry.
                if ($attempt < $maxAttempts) {
                    $browser->script("document.body.click();");
                    $browser->pause(300);
                }
            }
        }

        Assert::assertTrue($placed, "Failed to place '{$playerName}' into squad {$squadIndex}, category '{$categoryName}' after {$maxAttempts} attempts");

        // Brief pause for Vue to finish processing before the next fill call
        $browser->pause(200);
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
     * Slugify a category name the same way the JS slugify() helper does.
     *
     * "1. MD" → "1-md", "3. HS" → "3-hs", "2. DD" → "2-dd", etc.
     * Must match resources/js/admin-v2/helpers.js slugify() exactly.
     */
    public static function slugifyCategory(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/\s+/', '-', $text);       // spaces → dashes
        $text = preg_replace('/[^\w-]+/', '', $text);     // remove non-word chars (except dash)
        $text = preg_replace('/--+/', '-', $text);        // collapse multiple dashes
        $text = trim($text, '-');                          // trim leading/trailing dashes
        return $text;
    }

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

    /**
     * Scroll the validation section into view and wait for it to render.
     */
    private function scrollToValidation(Browser $browser): void
    {
        $browser->waitFor('@validation-incomplete-team', 10);
        $browser->script("
            var el = document.querySelector(\"[dusk='validation-incomplete-team']\");
            if (el) el.scrollIntoView({block: 'center'});
        ");
        $browser->pause(500);
    }

    /**
     * Assert "Fuldendt hold" shows "Fejl" (incomplete team detected).
     *
     * When basic validation fails, the other two checks are gated and show "-".
     */
    public function assertIncompleteTeamFailing(Browser $browser): void
    {
        $this->scrollToValidation($browser);
        $browser->assertSeeIn('@validation-incomplete-team', 'Fejl')
            ->assertSeeIn('@validation-invalid-level', '-')
            ->assertSeeIn('@validation-invalid-category', '-');
    }

    /**
     * Assert "Spiller for højt i kategorien" shows "Fejl".
     *
     * Basic validation must pass (OK) for this check to run.
     */
    public function assertCategoryValidationFailing(Browser $browser): void
    {
        $this->scrollToValidation($browser);
        $browser->assertSeeIn('@validation-incomplete-team', 'OK')
            ->assertSeeIn('@validation-invalid-category', 'Fejl');
    }

    /**
     * Assert "Spiller på et forkert hold" shows "Fejl".
     *
     * Basic validation must pass (OK) for this check to run.
     */
    public function assertLevelValidationFailing(Browser $browser): void
    {
        $this->scrollToValidation($browser);
        $browser->assertSeeIn('@validation-incomplete-team', 'OK')
            ->assertSeeIn('@validation-invalid-level', 'Fejl');
    }
}
