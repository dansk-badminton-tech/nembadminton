<?php

namespace FlyCompany\BadmintonPlayerAPI\Models;

class PlayerRanking
{
    /**
     * Number of player in system
     * @var string|null
     */
    public ?string $playerNumber;

    /**
     * Name
     * @var string|null
     */
    public ?string $name;

    /**
     * Gender
     * @var string|null
     */
    public ?string $gender;

    /**
     * Number of points in single discipline
     * @var int|null
     */
    public ?int $singlePoints;

    /**
     * Number of points in double discipline
     * @var int|null
     */
    public ?int $doublePoints;

    /**
     * Number of points in mixed gender discipline
     * @var int|null
     */
    public ?int $mixPoints;


}
