<?php

namespace Tests\Browser\Pages;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
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
            '@ranking-list-select' => "[dusk='ranking-list-select']",
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
     * Clicks the segmented radio button matching the given value.
     * Valid values: WOMEN_SINGLE, MEN_SINGLE, WOMENS_DOUBLE, MENS_DOUBLE,
     *               WOMEN_MIX, MEN_MIX.
     */
    public function switchRankingList(Browser $browser, string $value): void
    {
        $selector = "[dusk='ranking-list-{$value}']";
        $browser->waitFor($selector)
            ->scrollIntoView($selector)
            ->click($selector);
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
            $browser->waitFor("[dusk='player-search-panel'] table tbody tr [dusk='add-player-button-1']", 10);

            $browser->click("[dusk='player-search-panel'] table tbody tr [dusk='add-player-button-1']");

            // addSquadMemberByRefId awaits a full team round refetch before resolving,
            // which grows slower as more categories/players are added (e.g. 13-kamps).
            $browser->waitForText('Tilføjet til Hold', 20)
                ->pause(1000);
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

    public function autoFillCategory(Browser $browser, string $category, string $playerName): void
    {
        $browser->waitFor("[dusk='$category']");
        $browser->click("[dusk='$category']");
        $browser->waitFor("[dusk='is-in-squad']");
        $browser->waitForText($playerName);
        $browser->clickLink($playerName);
    }

    /**
     * Fill an inline autocomplete slot within a specific squad and category.
     *
     * Uses dusk selectors for targeting:
     *   - [dusk='squad-{N}'] scopes to the correct squad
     *   - [dusk='player-search-autocomplete-{slug}'] scopes to the correct category
     *
     * Approach:
     *   1. Dismiss any stale dropdown, scroll the input into view
     *   2. Type the first 5 chars of the player name via Dusk (real keystrokes
     *      that trigger Buefy's @typing handler and Apollo search)
     *   3. Wait for the player name to appear in the autocomplete dropdown
     *   4. Click the matching dropdown item
     *   5. Verify the player was placed — retry the full sequence if not
     *
     * @param int    $squadIndex   0-based squad index (0 = Hold 1, 1 = Hold 2, etc.)
     * @param string $categoryName Category label as shown in the <th>, e.g. "1. DD"
     * @param string $playerName   Full player name to search for
     */
    public function fillCategorySlot(Browser $browser, int $squadIndex, string $categoryName, string $playerName): void
    {
        $categorySlug = self::slugifyCategory($categoryName);

        // Dusk selector scoped to the correct squad + category.
        // Buefy passes the dusk attribute directly to the <input> element.
        $inputSelector = "[dusk='squad-{$squadIndex}'] [dusk='player-search-autocomplete-{$categorySlug}']";

        $maxAttempts = 3;
        $placed = false;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // Brief pause to let Vue finish any pending re-renders from the
            // previous player placement (prevents stale element references).
            $browser->pause(300);

            try {
                // Wait for the input to be present and enabled.
                $browser->waitFor($inputSelector)
                    ->waitUntilEnabled($inputSelector);

                // type() clears the field and sends real keystrokes that trigger
                // Buefy's @typing handler and the debounced Apollo search.
                $browser->clear($inputSelector);
                $browser->type($inputSelector, $playerName);

                // Wait for the player name to appear in the autocomplete dropdown.
                $browser->waitForTextIn(
                    '.autocomplete .dropdown-content',
                    $playerName,
                    10
                );
            } catch (StaleElementReferenceException $e) {
                // Vue re-rendered the input between find and interact — retry.
                if ($attempt === $maxAttempts) {
                    Assert::fail("StaleElementReferenceException for '{$playerName}' (squad {$squadIndex}, {$categoryName}) after {$maxAttempts} attempts");
                }
                continue;
            } catch (\Facebook\WebDriver\Exception\TimeoutException $e) {
                if ($attempt === $maxAttempts) {
                    Assert::fail("Timed out waiting for '{$playerName}' in dropdown (squad {$squadIndex}, {$categoryName}) after {$maxAttempts} attempts");
                }
                continue;
            }

            $browser->clickLink($playerName);

            // Verify the player was placed (name appears within the squad div)
            try {
                $browser->waitForTextIn("[dusk='squad-{$squadIndex}']", $playerName, 3);
                $placed = true;
                break;
            } catch (\Facebook\WebDriver\Exception\TimeoutException $e) {
                // Retry
            }
        }

        Assert::assertTrue($placed, "Failed to place '{$playerName}' into squad {$squadIndex}, category '{$categoryName}' after {$maxAttempts} attempts");
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
        // Poll until Vue has finished re-rendering after the last player
        // placement — the final autocomplete input may still be in the DOM
        // for a brief moment after fillCategorySlot returns.
        $browser->waitUsing(5, 200, function () use ($browser) {
            $emptyInputs = $browser->script("
                return document.querySelectorAll(\"[dusk='team-table-section'] input[placeholder='Søg på spiller...']\").length;
            ");
            return ($emptyInputs[0] ?? 0) === 0;
        }, 'All player slots should be filled (still found empty autocomplete inputs after 5s)');
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
        $browser->waitForTextIn('@validation-incomplete-team', 'OK', 10)
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
        $browser->waitForTextIn('@validation-incomplete-team', 'Fejl', 10)
            ->waitForTextIn('@validation-invalid-level', 'Afventer', 10)
            ->waitForTextIn('@validation-invalid-category', 'Afventer', 10);
    }

    /**
     * Assert "Spiller for højt i kategorien" shows "Fejl".
     *
     * Basic validation must pass (OK) for this check to run.
     */
    public function assertCategoryValidationFailing(Browser $browser): void
    {
        $this->scrollToValidation($browser);
        $browser->waitForTextIn('@validation-incomplete-team', 'OK', 10)
            ->waitForTextIn('@validation-invalid-category', 'Fejl', 10);
    }

    /**
     * Assert "Spiller på et forkert hold" shows "Fejl".
     *
     * Basic validation must pass (OK) for this check to run.
     */
    public function assertLevelValidationFailing(Browser $browser): void
    {
        $this->scrollToValidation($browser);
        $browser->waitForTextIn('@validation-incomplete-team', 'OK', 10)
            ->waitForTextIn('@validation-invalid-level', 'Fejl', 10);
    }
}
