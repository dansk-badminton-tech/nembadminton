<?php

declare(strict_types=1);


namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Support\Arr;
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

    /**
     * @param  array|Squad[]  $squads
     */
    public function validateCrossSquadsLeague(array $squads): Collection
    {
        $count = count($squads);
        if (empty($squads) || $count === 1) {
            return new Collection();
        }
        if ($count !== 2) {
            throw new \RuntimeException('There must be two teams for league + 1 div validation');
        }
        [$upperTeam, $lowerTeam] = $squads;
        $lowerTeam = new Collection($lowerTeam);
        $lowerTeamPlayers = (new Collection($lowerTeam->get('categories')))->pluck('players')->flatten(1)->unique(
            'refId'
        );

        $totalHits = new Collection();
        /** @var Player[] $lowerTeamPlayers */
        foreach ($lowerTeamPlayers as $lowerPlayer) {
            if (!$this->isYoungPlayer($lowerPlayer)) {
                $hits = $this->compareEveryPlayerInEveryCategory($upperTeam, $lowerPlayer);
                $totalHits->put($lowerPlayer->refId, $hits);
            }
        }

        $conflicts = new Collection();

        //dd($totalHits);

        /** @var Collection[] $totalHits */
        foreach ($totalHits as $hits) {
            foreach ($hits as $hit) {
                $yes = $hit->filter(function ($balance) {
                    return $balance['balance'] < 0;
                });
                if ($yes->count() === $hit->count()) {
                    foreach ($hit as $stuff) {
                        $currentAbovePlayer = $stuff['target'];
                        $currentBelowPlayer = $stuff['source'];
                        $currentCategory = $stuff['category'];
                        $this->addOrUpdateBelowPlayers(
                            $conflicts,
                            $currentAbovePlayer,
                            $currentCategory,
                            $currentBelowPlayer
                        );
                    }
                }
            }
        }

        return $conflicts;
    }

    private function compareEveryPlayerInEveryCategory(Squad $leagueSquad, Player $belowPlayer): Collection
    {
        $limit = 50;
        $hit = new Collection();
        foreach ($leagueSquad->categories as $leagueCategory) {
            foreach ($leagueCategory->players as $abovePlayer) {
                if ($abovePlayer->gender !== $belowPlayer->gender || $this->isYoungPlayer($abovePlayer)) {
                    continue;
                }

                try {
                    $belowPlayerCategoryPoint = $this->getPlayerCategoryPoint($belowPlayer, $leagueCategory->category);
                } catch (PointNotFoundInCategoryException $categoryException) {
                    $belowPlayerCategoryPoint = null;
                }
                    $abovePlayerCategoryPoint = $this->getPlayerCategoryPoint($abovePlayer, $leagueCategory->category);

                // If below player is not placed in the category ranking it is marked as valid
                if ($belowPlayerCategoryPoint === null) {
                    $balance = 0;
                } else {
                    $balance = $abovePlayerCategoryPoint - $belowPlayerCategoryPoint + $limit;
                }
                if ($hit->has($abovePlayer->refId)) {
                    $hit->get($abovePlayer->refId)->push([
                        'balance' => $balance,
                        'category' => $leagueCategory->category,
                        'source' => $belowPlayer,
                        'target' => $abovePlayer
                    ]);
                } else {
                    $hit->put(
                        $abovePlayer->refId,
                        new Collection([
                            [
                                'balance' => $balance,
                                'category' => $leagueCategory->category,
                                'source' => $belowPlayer,
                                'target' => $abovePlayer
                            ]
                        ])
                    );
                }
            }
        }
        return $hit;
    }

    /**
     * @param  array|Squad[]  $squads
     * @return Collection
     */
    public function validateCrossSquads(array $squads): Collection
    {
        $limit = 50;

        // Making a list of players grouped by squad and category
        $squadsC = new Collection($squads);
        $teamCategories = $squadsC->pluck('categories');
        $categories = new Collection();
        foreach ($teamCategories as $teamCategory) {
            $teamCategory = new Collection($teamCategory);
            $groupedByCategory = $teamCategory->groupBy('category');
            foreach ($groupedByCategory as $category => $players) {
                if (!$categories->has($category)) {
                    $categories->put($category, new Collection());
                }
                $categories->get($category)->push(new Collection(collect($players)->pluck('players')->collapse()));
            }
        }

        // Finding conflicts only scoped to categorize
        $hits = new Collection();
        /** @var Collection[] $categories */
        foreach ($categories as $categoryName => $category) {
            while ($category->isNotEmpty()) {
                /** @var Collection $currentAbovePlayers */
                $currentAbovePlayers = $category->shift();
                foreach ($currentAbovePlayers as $currentAbovePlayer) {
                    $allBelowPlayers = $category->collapse();
                    foreach ($allBelowPlayers as $belowPlayer) {
                        $belowPlayerPoints = $this->getPlayerLevel($belowPlayer);
                        $abovePlayerPoints = $this->getPlayerLevel($currentAbovePlayer);
                        if ($belowPlayerPoints > ($abovePlayerPoints + $limit) && $currentAbovePlayer->gender === $belowPlayer->gender && !$this->isYoungPlayer($belowPlayer)) {
                            $hits->push([$categoryName, $currentAbovePlayer, $belowPlayer]);
                        }
                    }
                }
            }
        }

        // Finding valid conflicts. A conflict is only valid if the same players are in conflict in the same categories
        $conflict = new Collection();
        while ($hits->isNotEmpty()) {
            [$currentCategory, $currentAbovePlayer, $currentBelowPlayer] = $hits->shift();
            $playerInOtherCategory = $hits->first(
                static function (array $players) use ($currentAbovePlayer, $currentBelowPlayer) {
                    [, $abovePlayer, $belowPlayer] = $players;
                    return $abovePlayer->refId === $currentAbovePlayer->refId && $belowPlayer->refId === $currentBelowPlayer->refId;
                }
            );
            if ($playerInOtherCategory !== null) {
                $this->addOrUpdateBelowPlayers($conflict, $currentAbovePlayer, $currentCategory, $currentBelowPlayer);
                [$category, $abovePlayer, $belowPlayer] = $playerInOtherCategory;
                $this->addOrUpdateBelowPlayers($conflict, $abovePlayer, $category, $belowPlayer);
            }
        }

        return $conflict->values();
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
                $playingToHigh = $this->handleDouble($squad, $category, $limitDouble, $playingToHigh);
            } else {
                $playingToHigh = $this->handleSingle($squad, $category, $limit, $playingToHigh);
            }
        }

        return $playingToHigh;
    }

    private function addOrAppend(array $playingToHigh, array $item): array
    {
        foreach ($playingToHigh as &$player) {
            if ($player['refId'] === $item['refId'] && $player['category'] === $item['category']) {
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

    private function getPlayerCategoryPoint(Player $player, string $category): int
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
     * @param  Collection  $conflict
     * @param $currentAbovePlayer
     * @param $currentCategory
     * @param $currentBelowPlayer
     */
    private function addOrUpdateBelowPlayers(
        Collection $conflict,
        $currentAbovePlayer,
        $currentCategory,
        $currentBelowPlayer
    ): void {
        /** @var Collection $currentConflictPlayer */
        $currentConflictPlayer = $conflict->first(function (Collection $player) use (
            $currentAbovePlayer,
            $currentCategory
        ) {
            return $player->get('category') === $currentCategory && $player->get(
                    'refId'
                ) === $currentAbovePlayer->refId;
        });
        if ($currentConflictPlayer !== null) {
            $currentConflictBelowPlayers = $currentConflictPlayer->get('belowPlayer');
            $array = [
                'id' => $currentBelowPlayer->id ?? 0,
                'refId' => $currentBelowPlayer->refId,
                'name' => $currentBelowPlayer->name,
                'gender' => $currentBelowPlayer->gender,
                'category' => $currentCategory,
                'isYouthPlayer' => $this->hasYoungPlayer([$currentBelowPlayer]),
            ];
            $currentConflictBelowPlayers[] = $array;
            $currentConflictPlayer->put('belowPlayer', $currentConflictBelowPlayers);
        } else {
            $conflict->push(
                new Collection([
                    'id' => $currentAbovePlayer->id ?? 0,
                    'refId' => $currentAbovePlayer->refId,
                    'name' => $currentAbovePlayer->name,
                    'category' => $currentCategory,
                    'gender' => $currentAbovePlayer->gender,
                    'isYouthPlayer' => $this->hasYoungPlayer([$currentAbovePlayer]),
                    'belowPlayer' => [
                        [
                            'id' => $currentBelowPlayer->id ?? 0,
                            'refId' => $currentBelowPlayer->refId,
                            'name' => $currentBelowPlayer->name,
                            'gender' => $currentBelowPlayer->gender,
                            'category' => $currentCategory,
                            'isYouthPlayer' => $this->hasYoungPlayer([$currentBelowPlayer])
                        ]
                    ],
                ])
            );
        }
    }

    /**
     * @param  array|Squad[]  $squads
     */
    public function validateBasicSquads(array $squads): Collection
    {
        $entries = new Collection();
        foreach ($squads as $index => $squad) {
            $validt = true;
            foreach ($squad->categories as $category) {
                if ($category->isMixDouble()) {
                    if ($category->amountOfMenPlayers() !== 1 || $category->amountOfWomenPlayers() !== 1) {
                        $validt = false;
                    }
                } elseif ($category->isMensDouble()) {
                    if ($category->amountOfMenPlayers() !== 2) {
                        $validt = false;
                    }
                } elseif ($category->isWomensDouble()) {
                    if ($category->amountOfWomenPlayers() !== 2) {
                        $validt = false;
                    }
                } elseif ($category->isMenSingle()) {
                    if ($category->amountOfMenPlayers() !== 1) {
                        $validt = false;
                    }
                } elseif ($category->isWomenSingle()) {
                    if ($category->amountOfWomenPlayers() !== 1) {
                        $validt = false;
                    }
                } else {
                    throw new \RuntimeException('Unknown category');
                }
            }

            $foundOnlyOne = null;
            if (in_array($squad->playerLimit, [10, 8])) {
                $players = (new Collection(Arr::pluck($squad->categories, 'players')))->flatten(1);
                $playersByRefId = $players->groupBy('refId');
                $foundOnlyOne = $playersByRefId->first(static function (Collection $players) {
                    return $players->count() !== 2;
                });
            }
            $entries->push([
                'index' => $index,
                'spotsFulfilled' => $validt,
                'missingPlayerInCategory' => $foundOnlyOne !== null
            ]);
        }
        return $entries;
    }

    /**
     *
     * @param  array|Player[]  $players
     */
    private function hasYoungPlayer(array $players): bool
    {
        foreach ($players as $player) {
            foreach ($player->points as $point) {
                if ($point->vintage !== null && $point->vintage !== '' && in_array(strtoupper($point->vintage),
                        ['U17', 'U19'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param  Player  $player
     * @return bool
     */
    private function isYoungPlayer(Player $player): bool
    {
        return $this->hasYoungPlayer([$player]);
    }

    /**
     * @param  Squad  $squad
     * @param $category
     * @param  int  $limitDouble
     * @param  array  $playingToHigh
     * @return array
     */
    private function handleDouble(Squad $squad, $category, int $limitDouble, array $playingToHigh): array
    {
        $pairsInCategory = $this->getPairByCategory($squad->categories, $category);
        while ($pairsInCategory->count() > 1) {
            $belowPair = $pairsInCategory->pop();
            $belowPairsPoints = $this->getPairPoints($belowPair, $category);
            foreach ($pairsInCategory as $abovePair) {
                $abovePairsPoints = $this->getPairPoints($abovePair, $category);
                [$player1, $player2] = $abovePair;
                if (($belowPairsPoints > $abovePairsPoints + $limitDouble) && (!$this->hasYoungPlayer([
                            $belowPair[0],
                            $belowPair[1]
                        ]) || $this->hasYoungPlayer([$player1, $player2]))) {
                    $playingToHigh = $this->addOrAppend($playingToHigh, [
                        'id' => $player1->id ?? 0,
                        'refId' => $player1->refId,
                        'name' => $player1->name,
                        'gender' => $player1->gender,
                        'category' => $category,
                        'isYouthPlayer' => $this->isYoungPlayer($player1),
                        'hasYouthPlayerPartner' => $this->isYoungPlayer($player2),
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
                        'id' => $player2->id ?? 0,
                        'refId' => $player2->refId,
                        'name' => $player2->name,
                        'gender' => $player2->gender,
                        'category' => $category,
                        'isYouthPlayer' => $this->isYoungPlayer($player2),
                        'hasYouthPlayerPartner' => $this->isYoungPlayer($player1),
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
        return $playingToHigh;
    }

    /**
     * @param  Squad  $squad
     * @param $category
     * @param  int  $limit
     * @param $playingToHigh
     * @return array|mixed
     */
    private function handleSingle(Squad $squad, $category, int $limit, $playingToHigh)
    {
        $playersInCategory = $this->getPlayersByCategory($squad->categories, $category);
        while ($playersInCategory->count() > 1) {
            $belowPlayer = $playersInCategory->pop();
            try {
                $belowPlayerPoints = $this->getPlayerCategoryPoint($belowPlayer, $category);
                /** @var Player $belowPlayer */
                foreach ($playersInCategory as $abovePlayer) {
                    $abovePlayerPoints = $this->getPlayerCategoryPoint($abovePlayer, $category);
                    if ($belowPlayerPoints > $abovePlayerPoints + $limit && (!$this->isYoungPlayer($belowPlayer) || $this->isYoungPlayer($abovePlayer))) {
                        $playingToHigh = $this->addOrAppend($playingToHigh, [
                            'id' => $abovePlayer->id ?? 0,
                            'refId' => $abovePlayer->refId,
                            'name' => $abovePlayer->name,
                            'category' => $category,
                            'gender' => $abovePlayer->gender,
                            'isYouthPlayer' => $this->isYoungPlayer($abovePlayer),
                            'hasYouthPlayerPartner' => false,
                            // Always false because its single and you don't have a partner in singles
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
        return $playingToHigh;
    }

}
