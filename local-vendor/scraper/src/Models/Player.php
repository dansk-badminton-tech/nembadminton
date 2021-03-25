<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

class Player
{

    private string  $name;

    private array   $points;

    private ?string $gender;

    private string  $refId;

    public function __construct(string $name, array $points, string $refId, string $gender = null)
    {
        $this->name = $name;
        $this->points = $points;
        $this->refId = $refId;
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getPoints() : array
    {
        return $this->points;
    }

    /**
     * @return string|null
     */
    public function getGender() : ?string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender(string $gender) : void
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getRefId() : string
    {
        return $this->refId;
    }

}
