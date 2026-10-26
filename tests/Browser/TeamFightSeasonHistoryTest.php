<?php

namespace Tests\Browser;

use App\Models\TeamRound;
use Database\Seeders\YouthTeamRoundSeeder;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

/**
 * The Season History modal on the seeded "Ungdom" holdrunder (see YouthTeamRoundSeeder), which follow
 * Aske Groth Jensen from Hold 2 · 1. HS in round 1 to Hold 1 · 3. HS in round 3.
 *
 * Rounds are indexed oldest first, with the seeded Season's rounds 1-3 (RealisticSeasonSeeder) in between:
 * 0 = "Ungdom - 1. runde", 1 = round 1, 2 = "Ungdom - 2. runde", 3 = round 2, 4 = round 3, 5 = the viewed round.
 */
class TeamFightSeasonHistoryTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private function openRound(Browser $browser, string $name, string $waitForPlayer): void
    {
        $teamRound = TeamRound::where('name', $name)->sole();

        $browser->resize(1920, 1080)
            ->visit(new LoginPage)
            ->loginSPA('testing@gmail.com', 'Test1234')
            ->visit(new TeamFightEditPage($teamRound->clubhouse_id, $teamRound->id))
            ->waitForText('Holdene i holdrunden')
            ->waitForTextIn("[dusk='squad-0']", $waitForPlayer);
    }

    public function test_the_modal_shows_the_players_places_per_category_across_the_season(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, YouthTeamRoundSeeder::CONFLICT_ROUND, 'Aske Groth Jensen');

            $browser->openSeasonHistory(0, '3. HS', 'Aske Groth Jensen')
                ->assertSeeIn('@season-history-modal', 'Aske Groth Jensen')
                ->assertSeeIn('@season-history-explanation', 'Rækkefølgen i herresingle på tværs af klubbens hold (1. hold øverst).')
                ->assertSeeIn('@season-history-explanation', 'Opstillinger, ikke resultater.')
                ->assertDontSeeIn('@season-history-explanation', 'I double deler makkerne plads.');

            // Only the player's categories, both in the current round; singles is the default.
            $browser->assertPresent("[dusk='season-history-category-HS']")
                ->assertPresent("[dusk='season-history-category-HD']")
                ->assertMissing("[dusk='season-history-category-DS']")
                ->assertSeeIn("[dusk='season-history-category-HS']", '· nu')
                ->assertPresent("[dusk='season-history-category-HS'] label.is-selected");

            // Newest first, with the place in HS and every placement with its partner.
            $this->assertSame(
                ['season-history-row-5', 'season-history-row-4', 'season-history-row-3', 'season-history-row-2', 'season-history-row-1', 'season-history-row-0'],
                $browser->script("return Array.from(document.querySelectorAll(\"[dusk='season-history-table'] tbody tr\")).map(tr => tr.getAttribute('dusk'))")[0],
            );
            $browser->assertSeasonHistoryRow(0, 'Ungdom - 1. runde')
                ->assertSeasonHistoryRow(0, 'nr. 5 · Hold 2 · 1. HS')
                ->assertSeasonHistoryRow(0, 'HD nr. 5 · Hold 2 · 2. HD m. Magnus Venge-Jep')
                ->assertSeasonHistoryRow(2, 'nr. 4 · Hold 1 · 4. HS')
                ->assertSeasonHistoryRow(5, 'nr. 3 · Hold 1 · 3. HS')
                ->assertSeasonHistoryRow(5, 'HD nr. 2 · Hold 1 · 2. HD m. Jesper Lauge Andersen');

            // The chart has the player and the players around them, labelled by place.
            $browser->assertSeeIn('@season-history-chart', 'Hold 2 · 1. HS')
                ->assertSeeIn('@season-history-chart', 'Jakob Christensen')
                ->assertPresent('@season-history-chart g.subject');

            $browser->selectSeasonHistoryCategory('HD')
                ->waitForTextIn('@season-history-explanation', 'herredouble')
                ->assertSeeIn('@season-history-explanation', 'I double deler makkerne plads.')
                ->assertSeeIn("[dusk='season-history-row-2']", 'nr. 3 · Hold 1 · 3. HD')
                // Partners on the same point share one label, one name per line.
                ->assertScript(
                    "[...document.querySelectorAll('[dusk=\"season-history-label\"]')]"
                    .".some(label => [...label.querySelectorAll('tspan')].map(name => name.textContent.trim()).join(' ') === 'Aske Groth Jensen / Jesper Lauge Andersen')",
                    true
                );
        });
    }

    public function test_opening_history_preselects_the_category_of_the_clicked_row(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, YouthTeamRoundSeeder::CONFLICT_ROUND, 'Aske Groth Jensen');

            $browser->openSeasonHistory(0, '2. HD', 'Aske Groth Jensen')
                ->assertPresent("[dusk='season-history-category-HD'] label.is-selected")
                ->assertSeeIn('@season-history-explanation', 'herredouble');

            $browser->click('@season-history-close')->waitUntilMissing('@season-history-modal');
            $browser->openSeasonHistory(0, '3. HS', 'Aske Groth Jensen')
                ->assertPresent("[dusk='season-history-category-HS'] label.is-selected");
        });
    }

    public function test_pointing_at_a_round_highlights_it_in_the_chart_and_the_table(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, YouthTeamRoundSeeder::CONFLICT_ROUND, 'Aske Groth Jensen');
            $browser->openSeasonHistory(0, '3. HS', 'Aske Groth Jensen');

            $browser->mouseover("[dusk='season-history-round-1']")
                ->assertSeasonHistoryHighlighted(1);

            $browser->mouseover("[dusk='season-history-row-0']")
                ->assertSeasonHistoryHighlighted(0);
        });
    }

    public function test_a_tap_highlights_the_round_on_a_phone(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, YouthTeamRoundSeeder::CONFLICT_ROUND, 'Aske Groth Jensen');
            $browser->resize(390, 844)
                ->openSeasonHistory(0, '3. HS', 'Aske Groth Jensen');

            // Full-screen: as wide as the viewport (headless Chrome may keep the window a little wider than asked).
            [$cardWidth, $viewportWidth] = $browser->script("return [Math.round(document.querySelector(\"[dusk='season-history-modal']\").getBoundingClientRect().width), window.innerWidth]")[0];
            $this->assertLessThanOrEqual(768, $viewportWidth);
            $this->assertSame($viewportWidth, $cardWidth);
            $closeRight = $browser->script("return document.querySelector(\"[dusk='season-history-close']\").getBoundingClientRect().right")[0];
            $this->assertLessThanOrEqual($viewportWidth, $closeRight, 'The close button is on screen');

            $browser->click("[dusk='season-history-row-2']")
                ->assertSeasonHistoryHighlighted(2);

            $browser->click("[dusk='season-history-round-0']")
                ->assertSeasonHistoryHighlighted(0);
        });
    }

    public function test_escape_the_x_and_a_click_outside_close_the_modal(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, YouthTeamRoundSeeder::CONFLICT_ROUND, 'Aske Groth Jensen');

            $browser->openSeasonHistory(0, '3. HS', 'Aske Groth Jensen');
            $browser->click('@season-history-close')
                ->waitUntilMissing('@season-history-modal');

            $browser->openSeasonHistory(0, '3. HS', 'Aske Groth Jensen');
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::ESCAPE);
            $browser->waitUntilMissing('@season-history-modal');

            $browser->openSeasonHistory(0, '3. HS', 'Aske Groth Jensen');
            // The card covers the middle of the background, so click the background itself.
            $browser->script("document.querySelector('.modal.is-active .modal-background').click()");
            $browser->waitUntilMissing('@season-history-modal');
        });
    }

    public function test_the_first_round_of_the_season_has_no_history(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, 'Ungdom - 1. runde', 'Jesper Lauge Andersen');

            $browser->openSeasonHistory(1, '1. HS', 'Aske Groth Jensen')
                ->assertSeeIn('@season-history-empty', 'Ingen tidligere holdrunder i sæsonen')
                ->assertMissing('@season-history-chart');
        });
    }
}
