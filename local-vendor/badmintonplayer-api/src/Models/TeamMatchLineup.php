<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

class TeamMatchLineup
{

    /**
     * @var TeamMatch
     */
    public TeamMatch $match;

    /**
     * @var CombinedTeamMatch[]|null
     */
    public ?array $combinedTeamMatches;

}
