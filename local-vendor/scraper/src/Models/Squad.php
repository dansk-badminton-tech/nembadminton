<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

class Squad
{

    /**
     * @var int
     */
    public int $playerLimit;

    /**
     * @var string|null
     */
    public ?string $league;

    /**
     * @var Category[]
     */
    public array $categories = [];

}
