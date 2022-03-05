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
     * @var string|null
     */
    public ?string $playerNumber;

    /**
     * Name
     * @var string|null
     */
    public ?string $name;

    /**
     * Gender
     * @var string|null
     */
    public ?string $gender;

    /**
     * Number of points in single discipline
     * @var int|null
     */
    public ?int $singlePoints;

    /**
     * Number of points in double discipline
     * @var int|null
     */
    public ?int $doublePoints;

    /**
     * Number of points in mixed gender discipline
     * @var int|null
     */
    public ?int $mixPoints;

    /**
     * Level points
     * @var int|null
     */
    public ?int $niveauPoints;

    /**
     * Club Identifier
     * @var int|null
     */
    public ?int $clubID;

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
