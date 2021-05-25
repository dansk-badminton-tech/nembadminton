<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

class Team
{

    public ?string $name;

    public ?string $leagueGroupId;

    public ?string $ageGroupId;

    public ?string $league;

    public ?Squad  $squad;

    public function __construct(string $name, ?Squad $squad = null)
    {
        $this->name = $name;
        $this->squad = $squad;
    }

}
