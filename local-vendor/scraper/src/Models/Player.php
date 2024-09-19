<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\BadmintonPlayerAPI\Vintage;

class Player
{

    public string $name;

    /**
     * @var array|Point[]
     */
    public array   $points;

    public ?string $gender;

    public string  $refId;

    public int $badmintonPlayerId;

    public string $rankingList;

    public string $vintage;

    /**
     * This can happen is a player is not plotted in on badmintonplayer.dk
     * @return bool
     */
    public function isNoBody() : bool {
        return $this->badmintonPlayerId === 0;
    }

    public function calculateVintage(?Carbon $season = null) : Vintage
    {
        return Util::calculateVintageByRefId($this->refId, $season);
    }

}
