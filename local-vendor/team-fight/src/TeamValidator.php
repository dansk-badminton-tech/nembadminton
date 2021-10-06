<?php

declare(strict_types=1);


namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;
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
        if(empty($squads) || $count === 1){
            return new Collection();
        }
        if ($count !== 2) {
            throw new \RuntimeException('There must be two teams for league + 1 div validation');
        }
        [$leagueTeamSquad, $firstDivSquad] = $squads;
        $firstDivSquad = new Collection($firstDivSquad);
        $firstDivPlayers = (new Collection($firstDivSquad->get('categories')))->pluck('players')->flatten(1)->unique('refId');

        $totalHits = new Collection();
        foreach ($firstDivPlayers as $divPlayer) {
            $hits = $this->compareEveryPlayerInEveryCategory($leagueTeamSquad, $divPlayer);
            if ($hits->isNotEmpty()) {
                $totalHits->push($hits);
            }
        }

        $totalHits = $totalHits->filter(static function (Collection $hit){
            return $hit->count() >= 2;
        });

        return $totalHits->flatten(1);
    }

    private function compareEveryPlayerInEveryCategory(Squad $leagueSquad, Player $divPlayer): Collection
    {
        $limit = 50;
        $hit = new Collection();
        foreach ($leagueSquad->categories as $leagueCategory) {
            foreach ($leagueCategory->players as $leaguePlayer) {
                if ($leaguePlayer->gender !== $divPlayer->gender) {
                    continue;
                }
                try {
                    $divPlayerCategoryPoint = $this->getPlayerCategoryPoint($divPlayer, $leagueCategory->category);
                    $leaguePlayerCategoryPoint = $this->getPlayerCategoryPoint($leaguePlayer, $leagueCategory->category);
                    if ($divPlayerCategoryPoint > ($leaguePlayerCategoryPoint + $limit)) {
                        $this->addOrUpdateBelowPlayers($hit, $leaguePlayer, $leagueCategory->category, $divPlayer);
                    }
                } catch (PointNotFoundInCategoryException $categoryException) {
                    // We will allow a player is not placed on the ranking list
                }
            }
        }
        return $hit;
    }

    private function getCategoriesByGender(string $gender): array
    {
        $genderUpper = strtoupper($gender);
        if ($genderUpper === 'M') {
            return [
                'MD',
                'HS',
                'HD'
            ];
        }

        if ($genderUpper === 'K') {
            return [
                'MD',
                'DS',
                'DD'
            ];
        }

        throw new \RuntimeException("Unknown gender '$gender'");
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
                        if ($belowPlayerPoints > ($abovePlayerPoints + $limit) && $currentAbovePlayer->gender === $belowPlayer->gender) {
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
                static function (array $players) use ($currentCategory, $currentAbovePlayer, $currentBelowPlayer) {
                    [$category, $abovePlayer, $belowPlayer] = $players;
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
                'category' => $currentCategory
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
                    'belowPlayer' => [
                        [
                            'id' => $currentBelowPlayer->id ?? 0,
                            'refId' => $currentBelowPlayer->refId,
                            'name' => $currentBelowPlayer->name,
                            'gender' => $currentBelowPlayer->gender,
                            'category' => $currentCategory
                        ]
                    ],
                ])
            );
        }
    }

}
