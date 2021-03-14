<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight\Models;

class Squad
{

    /**
     * @var int
     */
    public int $playerLimit;

    /**
     * @var Category[]
     */
    public array $categories = [];

}