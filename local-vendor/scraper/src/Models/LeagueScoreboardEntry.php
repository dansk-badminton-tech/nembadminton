<?php

declare(strict_types=1);

namespace FlyCompany\Scraper\Models;

class LeagueScoreboardEntry
{
    private LeagueScoreboardTeam $team;
    private int $numberOfFights;
    private int $numberOfFightWins;
    private int $numberOfWinSets;
    private int $numberOfLostSets;
    private int $totalPoints;

    public function __construct(LeagueScoreboardTeam $team, int $numberOfFights, int $numberOfFightWins, int $numberOfWinSets, int $numberOfLostSets, int $totalPoints)
    {
        $this->team = $team;
        $this->numberOfFights = $numberOfFights;
        $this->numberOfFightWins = $numberOfFightWins;
        $this->numberOfWinSets = $numberOfWinSets;
        $this->numberOfLostSets = $numberOfLostSets;
        $this->totalPoints = $totalPoints;
    }

    /**
     * @return LeagueScoreboardTeam
     */
    public function getTeam(): LeagueScoreboardTeam
    {
        return $this->team;
    }

    /**
     * @return int
     */
    public function getNumberOfFights(): int
    {
        return $this->numberOfFights;
    }

    /**
     * @return int
     */
    public function getNumberOfFightWins(): int
    {
        return $this->numberOfFightWins;
    }

    /**
     * @return int
     */
    public function getNumberOfWinSets(): int
    {
        return $this->numberOfWinSets;
    }

    /**
     * @return int
     */
    public function getNumberOfLostSets(): int
    {
        return $this->numberOfLostSets;
    }

    /**
     * @return int
     */
    public function getTotalPoints(): int
    {
        return $this->totalPoints;
    }

}
