<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class TeamFightCreatePage extends Page
{
    private int $clubhouseId;

    public function __construct(int $clubhouseId)
    {
        $this->clubhouseId = $clubhouseId;
    }

    public function url(): string
    {
        return '/app/c-' . $this->clubhouseId . '/team-fight/create';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page');
    }

    public function elements(): array
    {
        return [
            '@page' => "[dusk='team-fight-create-page']",
            '@name-input' => "[dusk='team-fight-name-input']",
            '@date-picker' => "[dusk='team-fight-date-picker']",
            '@ranking-select' => "[dusk='team-fight-ranking-select'] select",
            '@submit-button' => "[dusk='team-fight-submit-button']",
        ];
    }

    /**
     * Navigate the Buefy datepicker to a specific month/year and click a day.
     *
     * Buefy datepicker month/year selects don't respond to Dusk's native
     * select() — we must set the value via JS and dispatch input/change events.
     * Months are 0-indexed (0 = January, 6 = July, 11 = December).
     *
     * @param int $month 1-indexed month (1 = January, 7 = July, 12 = December)
     * @param int $year  Full year (e.g. 2025)
     * @param int $day   Day of the month (1-31)
     */
    public function selectDate(Browser $browser, int $month, int $year, int $day): void
    {
        $jsMonth = $month - 1; // Buefy months are 0-indexed

        $browser->click('@date-picker')
            ->waitFor('.datepicker .dropdown-content');

        // Set month
        $browser->script("
            var monthSel = document.querySelector('.datepicker .datepicker-header .select select');
            monthSel.value = '{$jsMonth}';
            monthSel.dispatchEvent(new Event('input'));
            monthSel.dispatchEvent(new Event('change'));
        ");

        // Set year (second <select> in the datepicker header)
        $browser->script("
            var yearSel = document.querySelectorAll('.datepicker .datepicker-header .select select')[1];
            yearSel.value = '{$year}';
            yearSel.dispatchEvent(new Event('input'));
            yearSel.dispatchEvent(new Event('change'));
        ");
        $browser->pause(300);

        // Click the day cell, skipping "is-nearby" cells (days from adjacent months)
        $browser->script("
            var cells = document.querySelectorAll('.datepicker .datepicker-body a.datepicker-cell');
            for (var i = 0; i < cells.length; i++) {
                if (cells[i].textContent.trim() === '{$day}' && !cells[i].classList.contains('is-nearby')) {
                    cells[i].click();
                    break;
                }
            }
        ");
        $browser->pause(300);
    }

    /**
     * Select a ranking version by matching its visible option text.
     *
     * Buefy's b-select wraps the native <select> — Dusk's select() sometimes
     * fails because the dusk attribute lands on a wrapper div. This method
     * finds the <option> whose text contains the given string and selects it
     * via JS with proper event dispatching.
     *
     * @param string $text Partial text to match in option labels (e.g. "Juli 2025")
     */
    public function selectRankingByText(Browser $browser, string $text): void
    {
        $escapedText = addslashes($text);

        $browser->waitFor('@ranking-select')
            ->script("
                var sel = document.querySelector(\"[dusk='team-fight-ranking-select'] select\");
                var options = sel.options;
                for (var i = 0; i < options.length; i++) {
                    if (options[i].text.indexOf('{$escapedText}') !== -1) {
                        sel.selectedIndex = i;
                        sel.dispatchEvent(new Event('input'));
                        sel.dispatchEvent(new Event('change'));
                        break;
                    }
                }
            ");
    }

    /**
     * Fill in and submit the create team fight form.
     */
    public function createTeamFight(Browser $browser, string $name, int $day, string $rankingVersion): void
    {
        $browser->waitUntilEnabled('@name-input')
            ->type('@name-input', $name);

        $this->selectDay($browser, $day);

        $browser->select('@ranking-select', $rankingVersion)
            ->click('@submit-button');
    }

    /**
     * Select a day from the open datepicker by day number (1-31).
     */
    public function selectDay(Browser $browser, int $day): void
    {
        $browser->click('@date-picker')
            ->waitFor('.datepicker .dropdown-content')
            ->script("document.querySelectorAll('.datepicker .datepicker-body a.datepicker-cell.is-selectable')[" . ($day - 1) . "].click()");
    }
}
