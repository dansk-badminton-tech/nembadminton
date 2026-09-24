<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

class TeamMatchLineup
{
    public TeamMatch $match;

    /**
     * @var CombinedTeamMatch[]|null
     */
    public ?array $combinedTeamMatches;
}
