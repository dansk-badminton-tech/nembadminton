<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Collections;

use FlyCompany\BadmintonPlayerAPI\Models\PlayerRanking;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property $items PlayerRanking[]
 */
class PlayersRankingCollection extends Collection
{

    public function getByClubId(int $clubId): self
    {
        return $this->filter(static function(PlayerRanking $playerRanking) use ($clubId) {
            return $playerRanking->clubID === $clubId;
        });
    }

    public function searchByName(string $name) : self{
        return $this->filter(static function(PlayerRanking $playerRanking) use ($name) {
            return Str::contains($playerRanking->name, $name);
        });
    }

    public function getByPlayerNumber(string $playerNumber) : PlayerRanking{
        return $this->first(static function(PlayerRanking $playerRanking) use ($playerNumber) {
            return $playerNumber === $playerRanking->playerNumber;
        });
    }

    public function getByPlayerNumbers(string $playerNumber){
        return $this->filter(static function(PlayerRanking $playerRanking) use ($playerNumber) {
            return $playerRanking->playerNumber === $playerNumber;
        });
    }

}
