<?php
declare(strict_types=1);

namespace Tests;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Point;

class CategoryFactory
{

    public static function makeWomen(string $category1PointName, int $category1Points, string $category2PointName, int $category2Points, int $levelPoints, string $refId): Player
    {
        return static::makePlayerWithPoint($category1PointName, $category2PointName, 'K', $refId, $category1Points, $category2Points, $levelPoints);
    }

    public static function makeMen(string $category1PointName, int $category1Points, string $category2PointName, int $category2Points, int $levelPoints, string $refId): Player
    {
        return static::makePlayerWithPoint($category1PointName, $category2PointName, 'M', $refId, $category1Points, $category2Points, $levelPoints);
    }

    public static function makePlayerWithPoint(string $category1PointName, string $category2PointName, string $gender, string $refId, int $category1Points = 0, int $category2Points = 0, int $levelPoints = 0): Player
    {
        $levelPoint = new Point();
        $levelPoint->points = $levelPoints;
        $levelPoint->position = 1;
        $levelPoint->category = null;
        $levelPoint->version = '2020-08-01';
        $levelPoint->vintage = null;

        $categoryPoint = new Point();
        $categoryPoint->points = $category1Points;
        $categoryPoint->position = 1;
        $categoryPoint->category = $category1PointName;
        $categoryPoint->version = '2020-08-01';
        $categoryPoint->vintage = null;

        $category2Point = new Point();
        $category2Point->points = $category2Points;
        $category2Point->position = 1;
        $category2Point->category = $category2PointName;
        $category2Point->version = '2020-08-01';
        $category2Point->vintage = null;


        $player = new Player();
        $player->name = "Player-$category1PointName-$category2PointName";
        $player->gender = $gender;
        $player->refId = $refId;
        $player->points[] = $levelPoint;
        $player->points[] = $categoryPoint;
        $player->points[] = $category2Point;

        return $player;
    }

    /**
     * Creates a category
     *
     * @param string $name
     * @param string $categoryName
     * @param Player $player1
     * @param Player|null $player2
     * @return Category
     */
    public static function makeCategory(string $name, string $categoryName, Player $player1, ?Player $player2 = null): Category
    {

        $category = new Category();
        $category->name = $name;
        $category->category = $categoryName;
        $category->players[] = $player1;
        if ($player2 !== null) {
            $category->players[] = $player2;
        }
        return $category;
    }

}
