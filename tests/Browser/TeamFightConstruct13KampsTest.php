<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamFightConstruct13KampsTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_user_can_construct_holdrunde_with_13_kamps_hold(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            // Step 1: Login
            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234');

            // Step 2: Create holdrunde
            $browser->visit(new TeamFightCreatePage($clubhouse->id))
                ->on(new TeamFightCreatePage($clubhouse->id));

            // Fill name
            $browser->waitUntilEnabled('@name-input')
                ->type('@name-input', '13 Kamps Holdrunde Test');

            // Select date: July 14, 2025
            $browser->click('@date-picker')
                ->waitFor('.datepicker .dropdown-content');

            // Navigate datepicker to July 2025
            $browser->script("
                var monthSel = document.querySelector('.datepicker .datepicker-header .select select');
                monthSel.value = '6';
                monthSel.dispatchEvent(new Event('input'));
                monthSel.dispatchEvent(new Event('change'));
            ");
            $browser->script("
                var yearSel = document.querySelectorAll('.datepicker .datepicker-header .select select')[1];
                yearSel.value = '2025';
                yearSel.dispatchEvent(new Event('input'));
                yearSel.dispatchEvent(new Event('change'));
            ");
            $browser->pause(300);

            // Click day 14
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

            // Select Juli 2025 ranking
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

            // Submit and wait for success
            $browser->click('@submit-button')
                ->waitForText('Dit hold er gemt');

            // Verify redirect to edit page
            $browser->assertPathContains('/team-fight/')
                ->assertPathContains('/edit')
                ->waitForText('Holdene i holdrunden')
                ->assertSee('13 Kamps Holdrunde Test');

            // Step 3: Add 13-kamps hold
            $browser->waitFor("[dusk='add-13-kamps-hold-button']")
                ->scrollTo("[dusk='add-13-kamps-hold-button']")
                ->click("[dusk='add-13-kamps-hold-button']");

            // Verify Hold 1 appears with all 13 categories
            $browser->waitForText('Hold 1')
                ->assertSee('1. MD')
                ->assertSee('2. MD')
                ->assertSee('1. DS')
                ->assertSee('2. DS')
                ->assertSee('1. HS')
                ->assertSee('2. HS')
                ->assertSee('3. HS')
                ->assertSee('4. HS')
                ->assertSee('1. DD')
                ->assertSee('2. DD')
                ->assertSee('1. HD')
                ->assertSee('2. HD')
                ->assertSee('3. HD');

            // Step 4: Add 10 unique players from the ranking list panel
            //   1. Dame Mix  → 2 women → auto-placed into 1.MD, 2.MD (first slot)
            //   2. Herre Mix → 2 men   → auto-placed into 1.MD, 2.MD (second slot)
            //   3. Dame Single → 2 women → auto-placed into 1.DS, 2.DS
            //   4. Herre Single → 4 men  → auto-placed into 1.HS–4.HS
            //   5. DD 1 & 2 → inline search with existing dame players
            //   6. HD 1, 2, 3 → inline search with existing herre players

            $browser->script("window.scrollTo(0, 0)");

            // Helper: switch ranking list filter via JS
            $switchRankingList = function (Browser $browser, string $value) {
                $browser->script("
                    var sel = document.querySelector(\"[dusk='ranking-list-select']\");
                    if (sel.tagName !== 'SELECT') sel = sel.querySelector('select') || sel;
                    sel.value = '{$value}';
                    sel.dispatchEvent(new Event('input'));
                    sel.dispatchEvent(new Event('change'));
                ");
                $browser->waitFor("[dusk='player-search-panel'] table tbody tr", 15);
            };

            // Helper: add N players by clicking the first + button N times
            $addPlayersFromList = function (Browser $browser, int $count) {
                for ($i = 0; $i < $count; $i++) {
                    $browser->waitFor("[dusk='player-search-panel'] table tbody tr", 10);

                    $browser->script("
                        var buttons = document.querySelectorAll(\"[dusk='player-search-panel'] table tbody tr [dusk='add-player-button']\");
                        if (buttons.length > 0) buttons[0].click();
                    ");

                    $browser->waitForText('Tilføjet til Hold', 10)
                        ->pause(500);
                }
            };

            // Helper: fill one inline autocomplete slot in the team table
            $fillInlineSlot = function (Browser $browser) {
                $browser->script("
                    var inputs = document.querySelectorAll(\"[dusk='team-table-section'] input[placeholder='Søg på spiller...']\");
                    if (inputs.length > 0) {
                        inputs[0].focus();
                        inputs[0].click();
                    }
                ");
                $browser->pause(500);

                $browser->script("
                    var inputs = document.querySelectorAll(\"[dusk='team-table-section'] input[placeholder='Søg på spiller...']\");
                    if (inputs.length > 0) {
                        var input = inputs[0];
                        var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                        nativeInputValueSetter.call(input, ' ');
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                ");
                $browser->waitFor('.autocomplete .dropdown-menu .dropdown-content .dropdown-item', 10);

                $browser->script("
                    var dropdown = document.querySelector('.autocomplete .dropdown-menu .dropdown-content .dropdown-item');
                    if (dropdown) dropdown.click();
                ");
                $browser->pause(500);
            };

            // --- 1. Dame Mix: add 2 women ---
            $switchRankingList($browser, 'WOMEN_MIX');
            $addPlayersFromList($browser, 2);

            // --- 2. Herre Mix: add 2 men ---
            $switchRankingList($browser, 'MEN_MIX');
            $addPlayersFromList($browser, 2);

            // --- 3. Dame Single: add 2 women ---
            $switchRankingList($browser, 'WOMEN_SINGLE');
            $addPlayersFromList($browser, 2);

            // --- 4. Herre Single: add 4 men ---
            $switchRankingList($browser, 'MEN_SINGLE');
            $addPlayersFromList($browser, 4);

            $browser->screenshot('13kamps-10-players-added');

            // --- 5. DD 1 & 2: assign existing dame players via inline search (4 slots) ---
            $browser->scrollTo("[dusk='team-table-section']");
            for ($i = 0; $i < 4; $i++) {
                $fillInlineSlot($browser);
            }

            $browser->screenshot('13kamps-dd-filled');

            // --- 6. HD 1, 2, 3: assign existing herre players via inline search (6 slots) ---
            for ($i = 0; $i < 6; $i++) {
                $fillInlineSlot($browser);
            }

            $browser->screenshot('13kamps-hd-filled');

            // Step 5: Verify all categories are populated
            $browser->assertSee('Hold 1')
                ->assertSee('1. MD')
                ->assertSee('2. MD')
                ->assertSee('1. DS')
                ->assertSee('2. DS')
                ->assertSee('1. HS')
                ->assertSee('2. HS')
                ->assertSee('3. HS')
                ->assertSee('4. HS')
                ->assertSee('1. DD')
                ->assertSee('2. DD')
                ->assertSee('1. HD')
                ->assertSee('2. HD')
                ->assertSee('3. HD');

            // Verify no empty player slots remain
            $emptyInputs = $browser->script("
                return document.querySelectorAll(\"[dusk='team-table-section'] input[placeholder='Søg på spiller...']\").length;
            ");
            $this->assertEquals(0, $emptyInputs[0], 'All player slots should be filled');

            // Step 6: Verify validation status indicators
            // After all categories are filled, the validation checks should run.
            // "Fuldendt hold" → OK, "Spiller på et forkert hold" → OK, "Spiller for højt i kategorien" → OK
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

            $browser->screenshot('13kamps-test-complete');
        });
    }
}
