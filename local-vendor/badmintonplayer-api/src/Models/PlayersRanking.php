<?php

namespace FlyCompany\BadmintonPlayerAPI\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;

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

    public function getVersionDateCarbon() : Carbon{
        return Carbon::createFromFormat('Y-m-d', Str::substr($this->versionDate, 0, 10));
    }

}
