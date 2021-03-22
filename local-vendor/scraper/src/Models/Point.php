<?php


namespace FlyCompany\Scraper\Models;


class Point
{

    private string $name;

    private int    $points;

    private int    $position;

    private string $vintage;

    private string $badmintonId;

    public function __construct(string $name, int $points, int $position, string $vintage, string $badmintonId)
    {
        $this->name = $name;
        $this->points = $points;
        $this->position = $position;
        $this->vintage = $vintage;
        $this->badmintonId = $badmintonId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @return string
     */
    public function getBadmintonId() : string
    {
        return $this->badmintonId;
    }

}
