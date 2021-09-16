<?php

declare(strict_types=1);


namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Support\Arr;
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
     * Validate multiple squads is complies with ranking point rules
     *
     * @param  array|Squad[]  $squads
     *
     * @return array|Squad[]
     */
    public function validateSquads(array $squads): array
    {
        $squads = new Collection($squads);
        $squads = $squads->sortKeysDesc();
        $squads = $squads->toArray();
        $limit = 50;

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
                foreach ($playersInCategory as $belowPlayer) {
                    foreach ($aboveSquads as $aboveSquad) {
                        /** @var Player[] $abovePlayers */
                        $abovePlayers = $this->getPlayersByCategory(
                            $aboveSquad->categories,
                            $category,
                            $belowPlayer->gender
                        );
                        foreach ($abovePlayers as $abovePlayer) {
                            $playerPoints = $this->getPlayerLevel($belowPlayer);
                            $abovePlayerPoints = $this->getPlayerLevel($abovePlayer) + $limit;
                            if ($playerPoints > $abovePlayerPoints) {
                                $possibleToHighPlayers[$belowPlayer->refId][] = [
                                    'id' => $abovePlayer->id ?? 0,
                                    'refId' => $abovePlayer->refId,
                                    'name' => $abovePlayer->name,
                                    'category' => $category,
                                    'gender' => $abovePlayer->gender,
                                    'belowPlayer' => [
                                        [
                                            'id' => $belowPlayer->id ?? 0,
                                            'refId' => $belowPlayer->refId,
                                            'name' => $belowPlayer->name,
                                            'gender' => $belowPlayer->gender,
                                            'category' => $category
                                        ],
                                    ],
                                ];
                            }
                        }
                    }
                }
            }
        }

        $validationMapping = [];
        foreach ($possibleToHighPlayers as $refId => $players) {
            $collection = new Collection($players);
            /** @var Collection $playersByRef */
            foreach ($collection->groupBy('refId') as $playersByRef) {
                if ($playersByRef->count() >= 2) {
                    $validationMapping = \array_merge($validationMapping, $playersByRef->toArray());
                }
            }
        }

        $collection = new Collection($validationMapping);
        $conflictsByName = $collection->groupBy("name");
        $conflictsByName->map(function (Collection $players) {
            foreach ($players->groupBy("category") as $category) {
                $category->pluck("belowPlayer");
            }
        });

        return $validationMapping;
    }

    /**
     * @param  Squad  $squad
     *
     * @return array
     */
    public function validateSquad(Squad $squad): array
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
                            $playingToHigh = $this->addOrAppend($playingToHigh, [
                                'id' => $abovePair[0]->id ?? 0,
                                'refId' => $abovePair[0]->refId,
                                'name' => $abovePair[0]->name,
                                'gender' => $abovePair[0]->gender,
                                'category' => $category,
                                'belowPlayer' => [
                                    [
                                        'id' => $belowPair[0]->id ?? 0,
                                        'refId' => $belowPair[0]->refId,
                                        'name' => $belowPair[0]->name,
                                        'gender' => $belowPair[0]->gender,
                                        'category' => $category
                                    ],
                                    [
                                        'id' => $belowPair[1]->id ?? 0,
                                        'refId' => $belowPair[1]->refId,
                                        'name' => $belowPair[1]->name,
                                        'gender' => $belowPair[1]->gender,
                                        'category' => $category
                                    ],
                                ],
                            ]);
                            $playingToHigh = $this->addOrAppend($playingToHigh, [
                                'id' => $abovePair[1]->id ?? 0,
                                'refId' => $abovePair[1]->refId,
                                'name' => $abovePair[1]->name,
                                'gender' => $abovePair[1]->gender,
                                'category' => $category,
                                'belowPlayer' => [
                                    [
                                        'id' => $belowPair[0]->id ?? 0,
                                        'refId' => $belowPair[0]->refId,
                                        'name' => $belowPair[0]->name,
                                        'category' => $category,
                                        'gender' => $belowPair[0]->gender
                                    ],
                                    [
                                        'id' => $belowPair[1]->id ?? 0,
                                        'refId' => $belowPair[1]->refId,
                                        'name' => $belowPair[1]->name,
                                        'gender' => $belowPair[1]->gender,
                                        'category' => $category
                                    ],
                                ],
                            ]);
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
                                $playingToHigh = $this->addOrAppend($playingToHigh, [
                                    'id' => $abovePlayer->id ?? 0,
                                    'refId' => $abovePlayer->refId,
                                    'name' => $abovePlayer->name,
                                    'category' => $category,
                                    'gender' => $abovePlayer->gender,
                                    'belowPlayer' => [
                                        [
                                            'id' => $belowPlayer->id ?? 0,
                                            'refId' => $belowPlayer->refId,
                                            'name' => $belowPlayer->name,
                                            'category' => $category,
                                            'gender' => $belowPlayer->gender
                                        ],
                                    ],
                                ]);
                            }
                        }
                    } catch (PointNotFoundInCategoryException $exception) {
                        continue;
                    }
                }
            }
        }

        // Removed duplicates and merge below players

        return $playingToHigh;
    }

    private function addOrAppend(array $playingToHigh, array $item): array
    {
        foreach ($playingToHigh as &$player) {
            if ($player['id'] === $item['id']) {
                $player['belowPlayer'] = array_merge($player['belowPlayer'], $item['belowPlayer']);

                return $playingToHigh;
            }
        }
        unset($player);
        $playingToHigh[] = $item;

        return $playingToHigh;
    }

    /**
     * @param  array  $pair
     * @param  string  $category
     *
     * @return int
     */
    private function getPairPoints(array $pair, string $category): int
    {
        return array_reduce($pair, function ($points, Player $player) use ($category) {
            return $points + $this->getPlayerCategoryPoint($player, $category);
        });
    }

    private function isDoubles(string $category): bool
    {
        return in_array($category, ['MD', 'HD', 'DD'], false);
    }

    /**
     * @param  array|Category[]  $categories
     * @param  string  $category
     * @param  string|null  $gender
     *
     * @return Collection|Player[]
     */
    private function getPlayersByCategory(array $categories, string $category, ?string $gender = null): Collection
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
     * @param  array|Category[]  $categories
     * @param  string  $category
     *
     * @return Collection
     */
    private function getPairByCategory(array $categories, string $category): Collection
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
     * @param  Player  $player
     *
     * @return int
     */
    private function getPlayerLevel(Player $player): int
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
        throw new PointNotFoundInCategoryException(
            "Could not find points in '{$category}' for player '{$player->name}'"
        );
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
