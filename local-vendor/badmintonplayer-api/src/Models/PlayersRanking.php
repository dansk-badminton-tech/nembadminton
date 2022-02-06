<?php

namespace FlyCompany\BadmintonPlayerAPI\Models;

class PlayersRanking
{

    /**
     * Version date of ranking list
     * @var string|null
     */
    public ?string $versionDate;

    /**
     * @var PlayerRanking[]
     */
    public array $playerRankings;

}
