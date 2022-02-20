<?php


namespace FlyCompany\BadmintonPlayerAPI\Models;


class Player
{

    /**
     * Player club identity
     * @var int
     */
    public int $clubId;

    /**
     * Gender
     * @var string|null
     */
    public ?string $gender;

    /**
     * Name
     * @var string|null
     */
    public ?string $name;

    /**
     * Represents value if this person is active player
     * @var bool
     */
    public bool $active;

    /**
     * Player Number fx. 900910-24
     * @var string|null
     */
    public ?string $playerNumber;

    /**
     * Discipline points
     * @var int|null
     */
    public ?int $disciplinePoints;

}
