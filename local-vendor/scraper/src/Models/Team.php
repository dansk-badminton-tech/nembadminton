<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

use FlyCompany\Scraper\Enums\Side;

class Team
{

    public ?string $name;

    public ?string $leagueGroupId;

    public ?string $ageGroupId;

    public ?string $leagueMatchId;

    public ?string $side;

    public ?Squad  $squad;

    public function __construct(string $name, ?Squad $squad = null, ?Side $side = null)
    {
        $this->name = $name;
        $this->squad = $squad;
        $this->side = $side?->value;
    }

}
