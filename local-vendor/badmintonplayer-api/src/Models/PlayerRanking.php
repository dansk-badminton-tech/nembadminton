<?php

namespace FlyCompany\BadmintonPlayerAPI\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\BadmintonPlayerAPI\Vintage;
use FlyCompany\Members\Enums\Category;

class PlayerRanking
{
    /**
     * Number of player in system. fx 900910-17
     */
    public ?string $playerNumber;

    /**
     * Name
     */
    public ?string $name;

    /**
     * Gender
     */
    public ?string $gender;

    /**
     * Number of points in single discipline
     */
    public ?int $singlePoints;

    /**
     * Number of points in double discipline
     */
    public ?int $doublePoints;

    /**
     * Number of points in mixed gender discipline
     */
    public ?int $mixPoints;

    /**
     * Level points
     */
    public ?int $niveauPoints = 0;

    /**
     * Club Identifier
     */
    public ?int $clubID;

    /**
     * From badminton Danmark: True så de spille de kampe således de vil blive vist normalt på rangliste false vil man skulle trykke vis alle
     * Translated: true means the player is "active", false means "in-active"
     */
    public bool $showAll;

    public function getVintage(): Vintage
    {
        return Util::calculateVintage($this->getBirthday());
    }

    public function getBirthday(): Carbon
    {
        return Carbon::createFromFormat('ymd', substr($this->playerNumber, 0, 6));
    }

    public function getSingleCategory(): Category
    {
        if (strtolower($this->gender) === 'm') {
            return Category::MENS_SINGLE;
        }

        return Category::WOMENS_SINGLE;
    }

    public function getDoubleCategory(): Category
    {
        if (strtolower($this->gender) === 'm') {
            return Category::MENS_DOUBLE;
        }

        return Category::WOMENS_DOUBLE;
    }

    public function getMixCategory(): Category
    {
        if (strtolower($this->gender) === 'm') {
            return Category::MEN_MIX;
        }

        return Category::WOMEN_MIX;
    }
}
