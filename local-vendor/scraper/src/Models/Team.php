<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

use FlyCompany\TeamFight\Models\Squad;

class Team
{

    public string $name;

    public Squad  $squad;

    public function __construct(string $name, Squad $squad)
    {
        $this->name = $name;
        $this->squad = $squad;
    }

}
