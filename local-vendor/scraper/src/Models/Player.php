<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

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

    /**
     * This can happen is a player is not plotted in on badmintonplayer.dk
     * @return bool
     */
    public function isNoBody() : bool {
        return $this->badmintonPlayerId === 0;
    }

}
