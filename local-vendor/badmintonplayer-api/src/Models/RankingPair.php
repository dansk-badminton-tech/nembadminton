<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

class RankingPair
{

    /**
     * @var PlayersRanking
     */
    public PlayersRanking $current;

    /**
     * @var PlayersRanking
     */
    public PlayersRanking $previous;

}
