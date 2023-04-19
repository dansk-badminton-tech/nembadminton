<?php

namespace FlyCompany\BadmintonPlayerAPI\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Collections\PlayersRankingCollection;
use Illuminate\Support\Str;
use Symfony\Component\Serializer\Annotation\Ignore;

class PlayersRanking
{

    /**
     * Version date of ranking list
     * @var string|null
     */
    public ?string $versionDate;

    /**
     * @var PlayerRankingIterator|PlayerRanking[]
     */
    public PlayerRankingIterator $playerRankings;

    public function getVersionDateCarbon() : Carbon{
        return Carbon::parse($this->versionDate );
    }

    /**
     * @return PlayersRankingCollection
     */
    public function getPlayerRankingCollection(): PlayersRankingCollection
    {
        return new PlayersRankingCollection($this->playerRankings);
    }

}
