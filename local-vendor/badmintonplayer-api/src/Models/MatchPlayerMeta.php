<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

use FlyCompany\Members\Enums\Category;

/**
 * Class MatchPlayerMeta
 * @package FlyCompany\BadmintonPlayerAPI\Models
 *
 */
class MatchPlayerMeta
{
    /**
     * Discipline code of match
     * @var string|null
     */
    public ?string $disciplineCode;

    /**
     * Discipline ranking points
     * @var int
     */
    public int $disciplineRanking;

    /**
     * LeagueGroupTeam identity
     * @var int
     */
    public int $leagueGroupTeamId;

    /**
     * Did the player NOT show up
     * @var bool
     */
    public bool $noShow;

    /**
     * unknown player
     * @var bool
     */
    public bool $unknown;

    /**
     * Player identity
     * @var int|null
     */
    public ?int $playerId;

    /**
     * Player index in order of team match
     * @var int
     */
    public int $playerIndex;

    /**
     * Player ranking points
     * @var int
     */
    public int $rankingPoints;

    /**
     * Don't know?
     * @var int
     */
    public int $rankingPointsCount;

    /**
     * The player
     * @var Player
     */
    public Player $player;

    public function getShortDiscipline() : string{
        return $this->disciplineRanking.". ".Discipline::from($this->disciplineCode)->shortName();
    }

    public function getDiscipline() : Discipline {
        return Discipline::from($this->disciplineCode);
    }

}
