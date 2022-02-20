<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Collections;

use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use Illuminate\Support\Collection;

class TeamMatchLineupCollection extends Collection
{

    /**
     * @var TeamMatchLineup[]
     */
    protected $items;

    public function getByClubId(int $clubId) : static {
        return $this->filter(static function(TeamMatchLineup $lineup) use ($clubId) {
            return $lineup->match->clubId1 === $clubId || $lineup->match->clubId2 === $clubId;
        });
    }

}
