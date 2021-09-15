<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TeamValidator
{

    private array $categories = [
        'MD',
        'DS',
        'DD',
        'HS',
        'HD',
    ];

    /**
     * @param array|Squad[] $squads
     *
     * @return array|Squad[]
     */
    public function validateSquads(array $squads) : array
    {

        $squads = new Collection($squads);
        $squads = $squads->sortKeysDesc();
        $squads = $squads->toArray();
        $limit = 50;

        $validationMapping = [];
        $possibleToHighPlayers = [];

        foreach ($squads as $teamLevel => $squad) {
            foreach ($this->categories as $category) {
                $playersInCategory = $this->getPlayersByCategory($squad->categories, $category);
                $offset = -1 * $teamLevel;
                if ($offset === 0) {
                    continue;
                }
                /** @var Squad[] $aboveSquads */
                $aboveSquads = array_slice($squads, $offset);
                /** @var Player[] $playersInCategory */
                foreach ($playersInCategory as $player) {
                    foreach ($aboveSquads as $aboveSquad) {
                        /** @var Player[] $abovePlayers */
                        $abovePlayers = $this->getPlayersByCategory($aboveSquad->categories, $category, $player->gender);
                        foreach ($abovePlayers as $abovePlayer) {
                            $playerPoints = $this->getPlayerLevel($player);
                            $abovePlayerPoints = $this->getPlayerLevel($abovePlayer) + $limit;
                            if ($playerPoints > $abovePlayerPoints) {
                                $possibleToHighPlayers[] = [
                                    'abovePlayer' => [
                                        'id'    => $abovePlayer->id ?? 0,
                                        'refId' => $abovePlayer->refId,
                                        'name'  => $abovePlayer->name,
                                        'gender' => $abovePlayer->gender,
                                        'category' => $category
                                    ],
                                    'player'      => [
                                        'id'    => $player->id ?? 0,
                                        'refId' => $player->refId,
                                        'name'  => $player->name,
                                        'gender' => $player->gender,
                                        'category' => $category
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
                    $validationMapping = \array_merge($validationMapping, $playersByRef->toArray());
                }
            }
        }

        return $validationMapping;
    }

    /**
     * @param Squad $squad
     *
     * @return array
     */
    public function validateSquad(Squad $squad) : array
    {
        $limit = 50;
        $limitDouble = 100;
        $playingToHigh = [];
        foreach ($this->categories as $category) {
            if ($this->isDoubles($category)) {
                $pairsInCategory = $this->getPairByCategory($squad->categories, $category);
                while ($pairsInCategory->count() > 1) {
                    $belowPair = $pairsInCategory->pop();
                    $belowPairsPoints = $this->getPairPoints($belowPair, $category);
                    foreach ($pairsInCategory as $abovePair) {
                        $abovePairsPoints = $this->getPairPoints($abovePair, $category);
                        if ($belowPairsPoints > $abovePairsPoints + $limitDouble) {
                            $playingToHigh[] = [
                                'id'    => $abovePair[0]->id ?? 0,
                                'refId' => $abovePair[0]->refId,
                                'name'  => $abovePair[0]->name,
                                'gender'  => $abovePair[0]->gender,
                                'category' => $category
                            ];
                            $playingToHigh[] = [
                                'id'    => $abovePair[1]->id ?? 0,
                                'refId' => $abovePair[1]->refId,
                                'name'  => $abovePair[1]->name,
                                'gender'  => $abovePair[1]->gender,
                                'category' => $category,
                            ];
                        }
                    }
                }
            } else {
                $playersInCategory = $this->getPlayersByCategory($squad->categories, $category);
                while ($playersInCategory->count() > 1) {
                    $belowPlayer = $playersInCategory->pop();
                    try {
                        $belowPlayerPoints = $this->getPlayerCategoryPoint($belowPlayer, $category);
                        /** @var Player $belowPlayer */
                        foreach ($playersInCategory as $abovePlayer) {
                            $abovePlayerPoints = $this->getPlayerCategoryPoint($abovePlayer, $category);
                            if ($belowPlayerPoints > $abovePlayerPoints + $limit) {
                                $playingToHigh[] = [
                                    'id'    => $abovePlayer->id ?? 0,
                                    'refId' => $abovePlayer->refId,
                                    'name'  => $abovePlayer->name,
                                    'category' => $category,
                                    'gender' => $abovePlayer->gender
                                ];
                            }
                        }
                    } catch (PointNotFoundInCategoryException $exception) {
                        continue;
                    }
                }
            }
        }

        return $playingToHigh;
    }

    /**
     * @param array  $pair
     * @param string $category
     *
     * @return int
     */
    private function getPairPoints(array $pair, string $category) : int
    {
        return array_reduce($pair, function ($points, Player $player) use ($category) {
            return $points + $this->getPlayerCategoryPoint($player, $category);
        });
    }

    private function isDoubles(string $category) : bool
    {
        return in_array($category, ['MD', 'HD', 'DD'], false);
    }

    /**
     * @param array|Category[] $categories
     * @param string           $category
     * @param string|null      $gender
     *
     * @return Collection|Player[]
     */
    private function getPlayersByCategory(array $categories, string $category, ?string $gender = null) : Collection
    {
        $categoriesGrouped = (new Collection($categories))->groupBy('category');
        /** @var Collection $category */
        $category = $categoriesGrouped->get($category);

        $players = $category->pluck('players')->flatten(1);

        if ($gender !== null) {
            $players = $players->filter(static function (Player $value, $key) use ($gender) {
                return $value->gender === $gender;
            });
        }

        return $players;
    }

    /**
     * @param array|Category[] $categories
     * @param string           $category
     *
     * @return Collection
     */
    private function getPairByCategory(array $categories, string $category) : Collection
    {
        $categoriesGrouped = (new Collection($categories))->groupBy('category');
        $pairs = [];
        /** @var Category $specificCategory */
        foreach ($categoriesGrouped->get($category) as $specificCategory) {
            $pairs[] = $specificCategory->players;
        }

        return new Collection($pairs);
    }

    /**
     * @param Player $player
     *
     * @return int
     */
    private function getPlayerLevel(Player $player) : int
    {
        $points = $player->points;
        foreach ($points as $point) {
            if ($point->category === null) {
                return (int)$point->points;
            }
        }
        throw new \RuntimeException('Bad lucky');
    }

    private function getPlayerCategoryPoint(Player $player, string $category)
    {
        $points = $player->points;
        foreach ($points as $point) {
            $category = $this->getRankingCategory($category, $player->gender);
            if ($point->category === $category) {
                return (int)$point->points;
            }
        }
        throw new PointNotFoundInCategoryException("Could not find points in '{$category}' for player '{$player->name}'");
    }

    private function getRankingCategory(string $category, string $gender)
    {
        if ($category === 'MD' && $gender === 'M') {
            return 'MxH';
        }
        if ($category === 'MD' && $gender === 'K') {
            return 'MxD';
        }

        return $category;
    }

}
