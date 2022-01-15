<?php

declare(strict_types=1);

namespace FlyCompany\Scraper;

use FlyCompany\Scraper\Models\LeagueScoreboardEntry;

class LeagueScoreboard
{
    private string $leagueName;
    private array $entries = [];

    public function __construct(string $leagueName)
    {
        $this->leagueName = $leagueName;
    }

    /**
     * @return string
     */
    public function getLeagueName(): string
    {
        return $this->leagueName;
    }

    /**
     * @param  LeagueScoreboardEntry  $leagueScoreboardEntry
     * @return void
     */
    public function addLeagueScoreboardEntry(LeagueScoreboardEntry $leagueScoreboardEntry): void
    {
        $this->entries[] = $leagueScoreboardEntry;
    }

    /**
     * @return LeagueScoreboardEntry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }


}
