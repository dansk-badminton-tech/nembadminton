<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\BadmintonPlayerAPI\Vintage;

class Player
{

    public ?int    $id;

    public string  $gender;

    public string  $name;

    public ?string $refId;

    // This is only used when we calculate cross teams
    private array $playingIn = [];

    /**
     * @var Point[]
     */
    public array $points = [];

    /**
     * @return array
     */
    public function getPlayingIn() : array
    {
        return $this->playingIn;
    }

    /**
     * @param array $playingIn
     */
    public function setPlayingIn(array $playingIn) : void
    {
        $this->playingIn = $playingIn;
    }

    public function calculateVintage(?Carbon $season = null) : Vintage
    {
        return Util::calculateVintageByRefId($this->refId, $season);
    }

}
