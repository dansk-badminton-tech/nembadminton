<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;

class TeamMatch
{
    /**
     * League match identity
     */
    public int $leagueMatchId;

    /**
     * Division name where match was happen
     */
    public ?string $divisionName;

    /**
     * Identity of age group which players should belong to
     */
    public ?string $ageGroupId;

    /**
     * Home team name
     */
    public ?string $teamName1;

    /**
     * Home team number
     */
    public ?int $clubId1;

    /**
     * Home league team identity
     */
    public ?int $leagueTeamId1;

    /**
     * Away team name
     */
    public ?string $teamName2;

    /**
     * Away club identity
     */
    public ?int $clubId2;

    /**
     * Away league team identity
     */
    public ?int $leagueTeamId2;

    /**
     * Away team name
     */
    public ?int $teamNumber2;

    /**
     * Date and time when match was or will happen
     */
    public ?string $matchTime;

    /**
     * Venue name where match was or will happen
     */
    public ?string $venueName;

    /**
     * Home team score
     */
    public ?int $score1;

    /**
     * Away team score
     */
    public ?int $score2;

    /**
     * Name of League group
     */
    public ?string $groupName;

    /**
     * Identity of season in which match was or will happen
     */
    public int $seasonId;

    /**
     * Number of points in niveau
     */
    public ?int $niveauPoints;

    /**
     * Club identifier
     */
    public ?int $clubID;

    public function getMatchTimeCarbon(): ?Carbon
    {
        return $this->matchTime !== null ? Carbon::createFromFormat('Y-m-d\TH:i:s', $this->matchTime) : null;
    }
}
