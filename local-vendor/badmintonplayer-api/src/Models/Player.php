<?php

namespace FlyCompany\BadmintonPlayerAPI\Models;

class Player
{
    /**
     * Player club identity
     */
    public int $clubId;

    /**
     * Gender
     */
    public ?string $gender;

    /**
     * Name
     */
    public ?string $name;

    /**
     * Represents value if this person is active player
     */
    public bool $active;

    /**
     * Player Number fx. 900910-24
     */
    public ?string $playerNumber;

    /**
     * Discipline points
     */
    public ?int $disciplinePoints;
}
