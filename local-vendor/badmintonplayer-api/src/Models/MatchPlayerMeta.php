<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

use FlyCompany\Members\Enums\Category;

/**
 * Class MatchPlayerMeta
 */
class MatchPlayerMeta
{
    /**
     * Discipline code of match
     */
    public ?string $disciplineCode;

    /**
     * Discipline ranking points
     */
    public int $disciplineRanking;

    /**
     * LeagueGroupTeam identity
     */
    public int $leagueGroupTeamId;

    /**
     * Did the player NOT show up
     */
    public bool $noShow;

    /**
     * unknown player
     */
    public bool $unknown;

    /**
     * Player identity
     */
    public ?int $playerId;

    /**
     * Player index in order of team match
     */
    public int $playerIndex;

    /**
     * Player ranking points
     */
    public int $rankingPoints;

    /**
     * Don't know?
     */
    public int $rankingPointsCount;

    /**
     * The player
     */
    public Player $player;

    public function getShortDiscipline(): string
    {
        return $this->disciplineRanking.'. '.Discipline::from($this->disciplineCode)->shortName();
    }

    public function getDiscipline(): Discipline
    {
        return Discipline::from($this->disciplineCode);
    }
}
