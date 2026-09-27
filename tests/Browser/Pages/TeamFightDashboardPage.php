<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;

class TeamFightDashboardPage extends Page
{
    private int $clubhouseId;

    public function __construct(int $clubhouseId)
    {
        $this->clubhouseId = $clubhouseId;
    }

    public function url(): string
    {
        return '/app/c-'.$this->clubhouseId.'/team-fight/dashboard';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitFor('@page');
    }

    /** @return array<string, string> */
    public function elements(): array
    {
        return [
            '@page' => "[dusk='team-fight-dashboard-page']",
            '@create-team-fight-link' => "[dusk='create-team-fight-link']",
            '@team-fights-table' => "[dusk='team-fights-table']",
        ];
    }

    public function selectSeasonFilter(Browser $browser, string $filter): void
    {
        $index = array_search($filter, ['current', 'previous', 'rest', 'all'], true);
        Assert::assertNotFalse($index, 'Unknown season filter: '.$filter);
        $browser->click("[dusk='team-fight-dashboard-page'] .notification .buttons > button:nth-child(".($index + 1).')');
    }

    public function waitForRound(Browser $browser, string $name): void
    {
        $browser->waitForTextIn('@team-fights-table', $name);
    }

    public function waitForRoundToDisappear(Browser $browser, string $name): void
    {
        $browser->waitUsing(10, 100, fn () => ! in_array($name, $this->visibleRoundNames($browser), true));
    }

    /** @param list<string> $values */
    public function assertRoundRow(Browser $browser, string $name, array $values): void
    {
        $nameJson = json_encode($name, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $row = $browser->script(<<<JS
            return Array.from(document.querySelectorAll("[dusk='team-fights-table'] tbody tr"))
                .find(row => row.querySelector('td a')?.textContent.trim() === {$nameJson})?.innerText;
        JS)[0];
        Assert::assertNotNull($row, 'Expected visible row for '.$name);
        foreach ($values as $value) {
            Assert::assertStringContainsString($value, $row);
        }
    }

    public function openRound(Browser $browser, string $name): void
    {
        $browser->clickLink($name);
    }

    public function assertRoundNamesInclude(Browser $browser, string ...$names): void
    {
        $visible = $this->visibleRoundNames($browser);
        foreach ($names as $name) {
            Assert::assertContains($name, $visible);
        }
    }

    public function assertRoundNamesExclude(Browser $browser, string ...$names): void
    {
        $visible = $this->visibleRoundNames($browser);
        foreach ($names as $name) {
            Assert::assertNotContains($name, $visible);
        }
    }

    /** @return list<string> */
    private function visibleRoundNames(Browser $browser): array
    {
        return $browser->script(<<<'JS'
            return Array.from(document.querySelectorAll("[dusk='team-fights-table'] tbody tr td:first-child a"))
                .map(link => link.textContent.trim());
        JS)[0];
    }

    public function assertFirstRound(Browser $browser, string $name): void
    {
        Assert::assertSame($name, $this->visibleRoundNames($browser)[0] ?? null);
    }

    public function waitForFirstRound(Browser $browser, string $name): void
    {
        $browser->waitUsing(10, 100, fn () => ($this->visibleRoundNames($browser)[0] ?? null) === $name);
    }

    public function sortByGameDate(Browser $browser): void
    {
        $browser->click("[dusk='team-fights-table'] th:nth-child(3)");
    }

    public function nextPage(Browser $browser): void
    {
        $browser->click("[dusk='team-fights-table'] .pagination-next");
    }

    public function waitForEmptyList(Browser $browser): void
    {
        $browser->waitForText('Kom i gang med din næste holdrunde planlægning her');
    }

    public function openEmptyListCreation(Browser $browser): void
    {
        $browser->click("[dusk='team-fight-dashboard-page'] .box.content a");
    }

    public function copyTeamRound(Browser $browser, string $name): void
    {
        $this->clickRowAction($browser, $name, 'Kopier holdrunden');
        $browser->waitFor('.dialog')
            ->click('.dialog .modal-card-foot .button:last-child')
            ->waitForText('Holdrunden kopiret');
    }

    public function deleteTeamRound(Browser $browser, string $name, bool $confirm): void
    {
        $this->clickRowAction($browser, $name, 'Slet holdrunden');
        $browser->waitFor('.dialog')
            ->click($confirm ? '.dialog .modal-card-foot .button:last-child' : '.dialog .modal-card-foot .button:first-child')
            ->waitUntilMissing('.dialog');
    }

    private function clickRowAction(Browser $browser, string $name, string $title): void
    {
        $nameJson = json_encode($name, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $titleJson = json_encode($title, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $browser->script(<<<JS
            const row = Array.from(document.querySelectorAll("[dusk='team-fights-table'] tbody tr"))
                .find(row => row.querySelector('td a')?.textContent.trim() === {$nameJson});
            Array.from(row.querySelectorAll('button')).find(button => button.title === {$titleJson}).click();
        JS);
    }
}
