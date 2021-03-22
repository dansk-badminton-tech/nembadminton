<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use Illuminate\Support\Collection;

class TeamValidator
{

    private array $categories = [
        'MD',
        'DS',
        'DD',
        'HS',
        'HD',
    ];

    public function validateSquads(array $squads) : array
    {

        $squads = new Collection($squads);
        $squads = $squads->sortKeysDesc();
        $limit = 50;

        $validationMapping = [];

        foreach ($squads as $teamLevel => $squad) {
            foreach ($this->categories as $category) {
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
                            if ($playerPoints > $abovePlayerPoints) {
                                $possibleToHighPlayers[] = [
                                    'abovePlayer' => [
                                        'id'    => $abovePlayer['id'],
                                        'refId' => $abovePlayer['refId'],
                                        'name'  => $abovePlayer['name'],
                                    ],
                                    'player'      => [
                                        'id'    => $player['id'],
                                        'refId' => $player['refId'],
                                        'name'  => $player['name'],
                                    ],
                                ];
                            }
                        }
                    }
                }
            }
        }
        $hits = [];
        foreach ($possibleToHighPlayers as $player) {
            $hits[$player['player']['refId']][] = $player['abovePlayer'];
        }

        foreach ($hits as $refId => $players) {
            $collection = new Collection($players);
            /** @var Collection $playersByRef */
            foreach ($collection->groupBy('refId') as $playersByRef) {
                if ($playersByRef->count() >= 2) {
                    $validationMapping = array_merge($validationMapping, $playersByRef->toArray());
                }
            }
        }

        return $validationMapping;
    }

    private function findPlayerById(string $id, array $players)
    {
        foreach ($squads as $teamLevel => $squad) {
            foreach ($this->categories as $category) {
                $playersInCategory = $this->getPlayersByCategory($squad['categories'], $category);
                foreach ($playersInCategory as $player) {
                    $lookingForId = $player["id"];
                }
            }
        }
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
