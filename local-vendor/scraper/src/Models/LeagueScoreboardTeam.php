<?php

declare(strict_types=1);

namespace FlyCompany\Scraper\Models;

class LeagueScoreboardTeam
{
    private string $name;
    private int $leagueGroupId;
    private int $ageGroupId;
    private int $leagueGroupTeamId;

    public function __construct(string $name, int $leagueGroupId, int $ageGroupId, int $leagueGroupTeamId)
    {
        $this->name = $name;
        $this->leagueGroupId = $leagueGroupId;
        $this->ageGroupId = $ageGroupId;
        $this->leagueGroupTeamId = $leagueGroupTeamId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLeagueGroupId(): int
    {
        return $this->leagueGroupId;
    }

    /**
     * @return int
     */
    public function getAgeGroupId(): int
    {
        return $this->ageGroupId;
    }

    /**
     * @return int
     */
    public function getLeagueGroupTeamId(): int
    {
        return $this->leagueGroupTeamId;
    }

}
