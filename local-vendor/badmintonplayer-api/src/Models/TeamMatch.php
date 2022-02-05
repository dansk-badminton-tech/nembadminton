<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

class TeamMatch
{
    /**
     * League match identity
     * @var int
     */
    public int $leagueMatchId;

    /**
     * Division name where match was happen
     * @var string|null
     */
    public ?string $divisionName;
    public ?string $ageGroupId;

    /**
     * {
    "leagueMatchId": 0,
    "divisionName": "string",
    "ageGroupId": "string",
    "teamName1": "string",
    "teamNumber1": 0,
    "clubId1": 0,
    "leagueTeamId1": 0,
    "teamName2": "string",
    "clubId2": 0,
    "leagueTeamId2": 0,
    "teamNumber2": 0,
    "matchTime": "2022-02-05T11:40:37.350Z",
    "venueName": "string",
    "score1": 0,
    "score2": 0,
    "groupName": "string",
    "seasonId": 0
    }
     */
}
