<?php

declare(strict_types=1);

namespace FlyCompany\Scraper\Models;

class Squad
{
    public int $playerLimit;

    public ?string $league;

    /**
     * @var Category[]
     */
    public array $categories = [];
}
