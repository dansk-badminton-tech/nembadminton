<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

class CombinedTeamMatch
{

    /**
     * @var MatchPlayerMeta[]|null
     */
    public ?array $teamPlayers;

    /**
     * @param int $clubId
     * @return MatchPlayerMeta[]
     */
    public function getPlayersByClubId(int $clubId) : array{
        return array_filter($this->teamPlayers, static function(MatchPlayerMeta $matchPlayerMeta) use ($clubId){
            return $matchPlayerMeta->player->clubId === $clubId;
        });
    }

}
