<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;

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

    /**
     * Identity of age group which players should belong to
     * @var string|null
     */
    public ?string $ageGroupId;

    /**
     * Home team name
     * @var string|null
     */
    public ?string $teamName1;

    /**
     * Home team number
     * @var int|null
     */
    public ?int $clubId1;

    /**
     * Home league team identity
     * @var int|null
     */
    public ?int $leagueTeamId1;

    /**
     * Away team name
     * @var string|null
     */
    public ?string $teamName2;

    /**
     * Away club identity
     * @var int|null
     */
    public ?int $clubId2;

    /**
     * Away league team identity
     * @var int|null
     */
    public ?int $leagueTeamId2;

    /**
     * Away team name
     * @var int|null
     */
    public ?int $teamNumber2;

    /**
     * Date and time when match was or will happen
     * @var string|null
     */
    public ?string $matchTime;

    /**
     * Venue name where match was or will happen
     * @var string|null
     */
    public ?string $venueName;

    /**
     * Home team score
     * @var int|null
     */
    public ?int $score1;

    /**
     * Away team score
     * @var int|null
     */
    public ?int $score2;

    /**
     * Name of League group
     * @var string|null
     */
    public ?string $groupName;

    /**
     * Identity of season in which match was or will happen
     * @var int
     */
    public int $seasonId;

    /**
     * Number of points in niveau
     * @var int|null
     */
    public ?int $niveauPoints;

    /**
     * Club identifier
     * @var int|null
     */
    public ?int $clubID;

    public function getMatchTimeCarbon(): ?Carbon
    {
        return $this->matchTime !== null ? Carbon::createFromFormat('Y-m-d\TH:i:s', $this->matchTime ) : null;
    }

}
