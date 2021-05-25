<?php


namespace FlyCompany\Scraper\Models;


use Carbon\Carbon;

class Point
{

    public int     $points;

    public int     $position;

    public string  $vintage;

    public ?string $category;

    public ?Carbon $version;

    public function __construct(int $points, int $position, string $vintage)
    {
        $this->points = $points;
        $this->position = $position;
        $this->vintage = $vintage;
    }

    /**
     * @return int
     */
    public function getPoints(): int
    {
        return $this->points;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getVintage() : string
    {
        return $this->vintage;
    }

    /**
     * @return string|null
     */
    public function getCategory() : ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     */
    public function setCategory(?string $category) : void
    {
        $this->category = $category;
    }

}
