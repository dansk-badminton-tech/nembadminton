<?php

namespace FlyCompany\Scraper\Models;

use Carbon\Carbon;

class Point
{
    public int $points;

    public int $position;

    public string $vintage;

    public ?string $category;

    public ?Carbon $version;

    public function __construct(int $points, int $position, string $vintage)
    {
        $this->points = $points;
        $this->position = $position;
        $this->vintage = $vintage;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getVintage(): string
    {
        return $this->vintage;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }
}
