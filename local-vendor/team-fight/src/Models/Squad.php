<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Models;

class Squad
{
    public ?string $id = null;

    public int $playerLimit;

    /** A Paper Squad (§38 stk. 7) skips the rank order on the team and the strength order against higher Squads. */
    public bool $isPaperSquad = false;

    /**
     * @var Category[]
     */
    public array $categories = [];

    /**
     * @var Reserve[]
     */
    public array $reserves = [];
}
