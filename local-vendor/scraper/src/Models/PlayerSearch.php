<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\BadmintonPlayerAPI\Vintage;

class PlayerSearch
{

    public string $name;

    public string $club;

    public string $badmintonPlayerInternalId;

    public string $refId;

    public string $gender;

    public function calculateVintage(?Carbon $season = null) : Vintage
    {
        return Util::calculateVintageByRefId($this->refId, $season);
    }

}
