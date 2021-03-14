<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

/**
 * Class TeamMatch
 *
 * @package FlyCompany\Scraper\Models
 */
class TeamMatch
{

    public Team $home;

    public Team $guest;

    public function __construct(Team $home, Team $guest)
    {
        $this->home = $home;
        $this->guest = $guest;
    }

}
