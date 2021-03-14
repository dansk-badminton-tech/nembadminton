<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight\Models;

class Player
{

    public string  $gender;

    public string  $name;

    public ?string $refId;

    /**
     * @var Point[]
     */
    public array $points = [];
}
