<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

use RuntimeException;

class Player
{

    public string $name;

    /**
     * @var array|Point[]
     */
    public array   $points;

    public ?string $gender;

    public string  $refId;

    public int $badmintonPlayerId;

    public string $rankingList;

    /**
     * This can happen is a player is not plotted in on badmintonplayer.dk
     * @return bool
     */
    public function isNoBody() : bool {
        return $this->badmintonPlayerId === 0;
    }

    public function getPlayerCategoryPoint(string $category): int
    {
        $points = $this->points;
        foreach ($points as $point) {
            $category = $this->getRankingCategory($category, $this->gender);
            if ($point->category === $category) {
                return (int)$point->points;
            }
        }
        throw new RuntimeException(
            "Could not find points in '{$category}' for player '{$this->name}'"
        );
    }

    /**
     * @param  string  $category
     * @param  string  $gender
     * @return string
     */
    private function getRankingCategory(string $category, string $gender) : string
    {
        if ($category === 'MD' && $gender === 'M') {
            return 'MxH';
        }
        if ($category === 'MD' && $gender === 'K') {
            return 'MxD';
        }

        return $category;
    }

    /**
     *
     * @return int
     */
    public function getLevelPoints(): int
    {
        $points = $this->points;
        foreach ($points as $point) {
            if ($point->category === null) {
                return $point->points;
            }
        }
        throw new \RuntimeException('Could not find any level points');
    }

}
