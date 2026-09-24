<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

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
            '@player-search-input' => "input[dusk='player-search-input']",
            '@ranking-list-select' => "[dusk='ranking-list-select']",
            '@player-search-table' => "[dusk='player-search-table']",
            '@team-table-section' => "[dusk='team-table-section']",
            '@add-teams-section' => "[dusk='add-teams-section']",
            '@add-13-kamps-hold-button' => "[dusk='add-13-kamps-hold-button']",
            '@validation-incomplete-team' => "[dusk='validation-incomplete-team']",
            '@validation-invalid-level' => "[dusk='validation-invalid-level']",
            '@validation-invalid-category' => "[dusk='validation-invalid-category']",
            '@scenario-guide-link' => "[dusk='scenario-guide-link']",
            '@scenario-selector-dropdown' => "[dusk='scenario-selector-dropdown']",
            '@create-scenario-button' => "[dusk='create-scenario-button']",
            '@scenario-draft-warning-banner' => "[dusk='scenario-draft-warning-banner']",
            '@promote-scenario-button' => "[dusk='promote-scenario-button']",
            '@rename-scenario-button' => "[dusk='rename-scenario-button']",
            '@delete-scenario-button' => "[dusk='delete-scenario-button']",
            '@open-add-member-button' => "[dusk='open-add-member-button']",
            '@add-member-ref-birthday-input' => "[dusk='add-member-ref-birthday-input']",
            '@add-member-ref-end-input' => "[dusk='add-member-ref-end-input']",
            '@add-member-name-input' => "[dusk='add-member-name-input']",
            '@add-member-gender-select' => "[dusk='add-member-gender-select']",
            '@add-member-club-select' => "[dusk='add-member-club-select']",
            '@add-member-save-button' => "[dusk='add-member-save-button']",
            '@edit-squad-member-button' => "[dusk='edit-squad-member-button']",
            '@manual-correction-indicator' => "[dusk='manual-correction-indicator']",
            '@edit-player-close-button' => "[dusk='edit-player-close-button']",
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
        $browser->waitFor($selector);
        $this->scrollToCenter($browser, $selector);
        $browser->click($selector);
        $browser->waitFor("[dusk='player-search-panel'] table tbody tr", 15);
    }

    public function searchMembers(Browser $browser, string $name): void
    {
        $browser->waitFor('@player-search-input');
        $this->replaceInputValue($browser, '@player-search-input', $name);
    }

    public function toggleMemberFilter(Browser $browser, string $filter): void
    {
        $selector = "[dusk='show-{$filter}-switch']";
        $this->scrollToCenter($browser, $selector);
        $browser->click($selector);
    }

    public function goToMemberSearchPage(Browser $browser, string $direction): void
    {
        $selector = "@player-search-panel .pagination-{$direction}";
        $this->scrollToCenter($browser, $selector);
        $browser->click($selector);
    }

    public function createLocalMember(Browser $browser, string $refId, string $name, string $gender, int $clubId, array $points): void
    {
        [$birthday, $endId] = explode('-', $refId);

        $browser->click('@open-add-member-button')
            ->waitFor('@add-member-name-input')
            ->type('@add-member-ref-birthday-input', $birthday)
            ->type('@add-member-ref-end-input', $endId)
            ->type('@add-member-name-input', $name)
            ->select('@add-member-gender-select', $gender)
            ->waitFor("@add-member-club-select option[value='{$clubId}']")
            ->select('@add-member-club-select', (string) $clubId);

        foreach ($points as $category => $value) {
            $browser->type("[dusk='add-member-points-{$category}']", (string) $value);
        }

        $browser->click('@add-member-save-button')
            ->waitForText('Spiller oprettet')
            ->waitUntilMissing('@add-member-name-input');
    }

    public function correctSquadMemberPoints(Browser $browser, int $squadIndex, string $category, int $points): void
    {
        $input = "[dusk='edit-player-points-{$category}']";

        $browser->click("[dusk='squad-{$squadIndex}'] @edit-squad-member-button")
            ->waitFor($input);
        $this->replaceInputValue($browser, $input, (string) $points);

        // Points are saved on EditPlayerModal's 500 ms debounce; let it fire, then wait for the mutation.
        $browser->pause(700)
            ->waitUsing(10, 100, function () use ($browser) {
                return ! str_contains($browser->attribute('@edit-player-close-button', 'class'), 'is-loading');
            }, 'Manual point correction did not finish saving')
            ->click('@edit-player-close-button')
            ->waitUntilMissing($input);
    }

    /**
     * Replace an input's value with real keystrokes. WebDriver's clear() does not
     * emit the input events Buefy listens to, so the old value would linger in Vue.
     */
    private function replaceInputValue(Browser $browser, string $selector, string $value): void
    {
        $browser->keys($selector, ['{control}', 'a'], $value === '' ? '{backspace}' : $value);
    }

    /**
     * Scroll an element to the middle of the viewport; Dusk's scrollIntoView
     * aligns it to the top, where the fixed navbar intercepts clicks.
     */
    private function scrollToCenter(Browser $browser, string $selector): void
    {
        $selectorJson = json_encode($browser->resolver->format($selector), JSON_THROW_ON_ERROR);
        $browser->script("document.querySelector({$selectorJson}).scrollIntoView({block: 'center'})");
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

            $availablePlayerSelector = "[dusk='player-search-panel'] table tbody tr:first-child [dusk^='available-player-']";
            $availablePlayerDusk = $browser->attribute($availablePlayerSelector, 'dusk');

            $browser->click("[dusk='player-search-panel'] table tbody tr [dusk='add-player-button-1']");

            // addSquadMemberByRefId awaits a full team round refetch before resolving,
            // which grows slower as more categories/players are added (e.g. 13-kamps).
            $browser->waitForText('Tilføjet til Hold', 20)
                ->waitUntilMissing("[dusk='{$availablePlayerDusk}']", 10);
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
        $this->submitSquad($browser);
    }

    public function addCustomSquad(Browser $browser, string $name, string $tier, array $categoryCounts): void
    {
        $browser->click("[dusk='custom-match-count-toggle']")
            ->assertDisabled('@add-13-kamps-hold-button');

        foreach ($categoryCounts as $category => $count) {
            $browser->type("[dusk='custom-category-count-{$category}']", (string) $count);
        }

        $browser->type("[dusk='squad-name-input']", $name)
            ->type("[dusk='squad-tier-input']", $tier)
            ->assertEnabled('@add-13-kamps-hold-button');
        $this->submitSquad($browser);
    }

    private function submitSquad(Browser $browser): void
    {
        $browser->script("document.querySelector(\"[dusk='add-13-kamps-hold-button']\").click()");
    }

    public function openSquadAction(Browser $browser, int $index, string $action): void
    {
        $browser->mouseover("[dusk='squad-actions-{$index}']")
            ->click("[dusk='{$action}-{$index}']");
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
     *   2. Clear with real keystrokes and type the player name so Buefy's
     *      reactive state and Apollo search stay synchronized
     *   3. Wait for the player name to appear in the autocomplete dropdown
     *   4. Click the matching dropdown item
     *   5. Verify the player was placed before moving to the next slot
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
        $inputSelectorJson = json_encode($inputSelector, JSON_THROW_ON_ERROR);
        $playerNameJson = json_encode($playerName, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $browser->waitFor($inputSelector)
            ->waitUntilEnabled($inputSelector);

        $browser->script(<<<JS
            const input = document.querySelector({$inputSelectorJson});
            input.scrollIntoView({block: 'center'});
            input.focus();
        JS);

        // WebDriver's element.clear() does not emit the input events Buefy needs.
        // That can leave the previous slot's search in component state.
        $browser->keys($inputSelector, ['{control}', 'a'], '{backspace}')
            ->waitUsing(3, 100, function () use ($browser, $inputSelector) {
                return $browser->attribute($inputSelector, 'value') === '';
            }, "Autocomplete input {$inputSelector} did not clear")
            ->keys($inputSelector, $playerName);

        $slotCount = count($browser->elements($inputSelector));

        $browser->waitUsing(20, 100, function () use ($browser, $inputSelectorJson, $playerNameJson) {
            return ($browser->script(<<<JS
                const input = document.querySelector({$inputSelectorJson});
                const option = Array.from(input?.closest('.autocomplete')?.querySelectorAll('.dropdown-item') ?? [])
                    .find(element => element.textContent.includes({$playerNameJson}));
                return option !== undefined;
            JS)[0] ?? false);
        }, "Player {$playerName} did not appear in the autocomplete dropdown");

        $browser->script(<<<JS
            const input = document.querySelector({$inputSelectorJson});
            const option = Array.from(input.closest('.autocomplete').querySelectorAll('.dropdown-item'))
                .find(element => element.textContent.includes({$playerNameJson}));
            option.click();
        JS);

        $browser->waitUsing(10, 100, function () use ($browser, $inputSelector, $slotCount) {
            return count($browser->elements($inputSelector)) < $slotCount;
        }, "Player {$playerName} was not placed in its category slot");
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

    // ─── Scenario lifecycle ─────────────────────────────────────────────

    public function createScenarioFromOfficial(Browser $browser, string $name): void
    {
        $browser->assertMissing('@scenario-draft-warning-banner');
        if ($browser->elements($browser->resolver->format('@scenario-selector-dropdown'))) {
            $browser->assertSeeIn('@scenario-selector-dropdown .dropdown-trigger', '(Officiel)');
        }
        $browser->click('@create-scenario-button')
            ->waitFor('.dialog input')
            ->assertSeeIn('.dialog', 'Officiel opstilling');
        $this->replaceInputValue($browser, '.dialog input', $name);
        $browser->click('.dialog .modal-card-foot .button:last-child')
            ->waitForTextIn('@scenario-draft-warning-banner', $name, 15);
    }

    public function selectScenario(Browser $browser, string $name): void
    {
        $this->scrollToCenter($browser, '@scenario-selector-dropdown');
        $browser->click('@scenario-selector-dropdown .dropdown-trigger')
            ->waitFor('@scenario-selector-dropdown .dropdown-menu');
        $label = json_encode($name, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $browser->script(<<<JS
            Array.from(document.querySelectorAll("[dusk='scenario-selector-dropdown'] .dropdown-item"))
                .find(item => item.querySelector('span:nth-child(2)')?.textContent.trim() === {$label}).click();
        JS);
        $browser->waitForTextIn('@scenario-selector-dropdown .dropdown-trigger', $name, 15);
    }

    public function assertScenarioSelected(Browser $browser, string $name, bool $official): void
    {
        $browser->waitForTextIn('@scenario-selector-dropdown .dropdown-trigger', $name, 15)
            ->assertSeeIn('@scenario-selector-dropdown .dropdown-trigger', $official ? '(Officiel)' : '(Udkast)');
        if ($official) {
            $browser->assertMissing('@scenario-draft-warning-banner');
        } else {
            $browser->assertSeeIn('@scenario-draft-warning-banner', $name);
        }
    }

    public function removeSquadMember(Browser $browser, int $squadIndex, string $name): void
    {
        $scope = json_encode("[dusk='squad-{$squadIndex}']", JSON_THROW_ON_ERROR);
        $label = json_encode($name, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $browser->script(<<<JS
            const player = Array.from(document.querySelectorAll({$scope} + ' [data-player-id-input]'))
                .find(input => input.parentElement.textContent.includes({$label}));
            player.parentElement.querySelector('button[title="Slet"]').click();
        JS);
        $browser->waitUsing(15, 100, function () use ($browser, $name) {
            return ! str_contains($browser->text('@team-table-section'), $name);
        }, "Member {$name} was not removed from the Scenario");
    }

    public function renameScenario(Browser $browser, string $name): void
    {
        $browser->click('@rename-scenario-button')->waitFor('.dialog input');
        $this->replaceInputValue($browser, '.dialog input', $name);
        $browser->click('.dialog .modal-card-foot .button:last-child')
            ->waitForTextIn('@scenario-draft-warning-banner', $name, 15);
    }

    public function cancelScenarioPromotion(Browser $browser): void
    {
        $browser->click('@promote-scenario-button')
            ->waitFor('.dialog')
            ->click('.dialog .modal-card-foot .button:first-child')
            ->waitUntilMissing('.dialog');
    }

    public function confirmScenarioPromotion(Browser $browser): void
    {
        $browser->click('@promote-scenario-button')
            ->waitFor('.dialog')
            ->click('.dialog .modal-card-foot .button:last-child')
            ->waitUntilMissing('@scenario-draft-warning-banner', 15);
    }

    public function cancelScenarioDeletion(Browser $browser): void
    {
        $browser->click('@delete-scenario-button')
            ->waitFor('.dialog')
            ->click('.dialog .modal-card-foot .button:first-child')
            ->waitUntilMissing('.dialog');
    }

    public function confirmScenarioDeletion(Browser $browser): void
    {
        $browser->click('@delete-scenario-button')
            ->waitFor('.dialog')
            ->click('.dialog .modal-card-foot .button:last-child')
            ->waitUntilMissing('@scenario-draft-warning-banner', 15);
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
