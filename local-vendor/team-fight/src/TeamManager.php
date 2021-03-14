<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use App\Models\Teams;
use Carbon\Carbon;

class TeamManager
{

    /**
     * @param string $name
     * @param Carbon $game_date
     * @param string $version
     *
     * @return Teams
     */
    public function createTeam(string $name, Carbon $game_date, string $version)
    {
        return new Teams(compact('name', 'game_date', 'version'));
    }

}
