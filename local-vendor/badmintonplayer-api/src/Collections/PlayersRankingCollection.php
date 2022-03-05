<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Collections;

use FlyCompany\BadmintonPlayerAPI\Models\PlayerRanking;
use Illuminate\Support\Collection;

/**
 * @property $items PlayerRanking[]
 */
class PlayersRankingCollection extends Collection
{

    public function getByClubId(int $clubId): PlayersRankingCollection
    {
        return $this->filter(static function(PlayerRanking $playerRanking) use ($clubId) {
            return $playerRanking->clubID === $clubId;
        });
    }


}
