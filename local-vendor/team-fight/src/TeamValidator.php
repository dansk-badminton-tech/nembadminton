<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use Illuminate\Support\Collection;

class TeamValidator
{

    public function validateSquads(array $squads) : array
    {

        $squads = new Collection($squads);
        $squads = $squads->sortKeysDesc();
        $limit = 50;

        $validationMapping = [];

        $categories = [
            'MD',
            'DS',
            'DD',
            'HS',
            'HD',
        ];

        foreach ($squads as $teamLevel => $squad) {
            foreach ($categories as $category) {
                $playersInCategory = $this->getPlayersByCategory($squad['categories'], $category);
                $offset = -1 * $teamLevel;
                if ($offset === 0) {
                    continue;
                }
                $aboveSquads = $squads->slice($offset);
                foreach ($playersInCategory as $player) {
                    foreach ($aboveSquads as $aboveSquad) {
                        $abovePlayers = $this->getPlayersByCategory($aboveSquad['categories'], $category, $player['gender']);
                        foreach ($abovePlayers as $abovePlayer) {
                            $playerPoints = $this->getPlayerLevel($player);
                            $abovePlayerPoints = $this->getPlayerLevel($abovePlayer) + $limit;
                            if ($playerPoints > $abovePlayerPoints && !isset($validationMapping[$abovePlayer['id']])) {
                                $validationMapping[$abovePlayer['id']] = [
                                    'id'            => $abovePlayer['id'],
                                    'playingToHigh' => true,
                                    'name'          => $abovePlayer['name'],
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $validationMapping;
    }

    /**
     * @param array       $categories
     * @param string      $category
     * @param string|null $gender
     *
     * @return Collection
     */
    protected function getPlayersByCategory(array $categories, string $category, ?string $gender = null) : Collection
    {
        $categoriesGrouped = (new Collection($categories))->groupBy('category');
        /** @var Collection $category */
        $category = $categoriesGrouped->get($category);

        $players = $category->pluck('players')->flatten(1);

        if ($gender !== null) {
            $players = $players->filter(static function ($value, $key) use ($gender) {
                return $value['gender'] === $gender;
            });
        }

        return $players;
    }

    public function getPlayerLevel(array $player) : int
    {
        $points = $player['points'] ?? [];
        foreach ($points as $point) {
            if ($point['category'] === null) {
                return (int)$point['points'];
            }
        }
        throw new \RuntimeException('Bad lucky');
    }

}
