<?php


namespace FlyCompany\Scraper\Models;


class Point
{

    private int    $points;

    private int    $position;

    private string $vintage;

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

}
