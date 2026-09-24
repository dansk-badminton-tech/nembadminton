<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Models;

class Squad
{
    public ?string $id = null;

    public int $playerLimit;

    /**
     * @var Category[]
     */
    public array $categories = [];
}
