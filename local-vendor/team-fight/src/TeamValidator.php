<?php

declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Support\Collection;
use RuntimeException;

class TeamValidator
{

    public function validateCrossSquadsLeagueV3(array $squads) : Collection
    {
        $count = count($squads);
        if (empty($squads) || $count === 1) {
            return new Collection();
        }

        $listOfInvalidPlayers = [];

        while ($lastTeam = array_pop($squads)) {
            $listOfInvalidPlayers[] = $this->compare($lastTeam, $squads);
        }

        return collect($listOfInvalidPlayers)->flatten(1)->map(function ($data) {
            /** @var Player $player */
            $player = $data['player'];

            $invalids = collect($data['invalid']);

            return new Collection([
                'id'            => $player->id ?? 0,
                'refId'         => $player->refId,
                'name'          => $player->name,
                'category'      => "Dummy-stuff",
                'gender'        => $player->gender,
                'isYouthPlayer' => $this->hasYoungPlayer([$player]),
                'belowPlayer'   => $invalids->map(function (array $invalidData) {
                    /** @var Player $player */
                    $player = $invalidData['player'];

                    return [
                        'id'            => $player->id ?? 0,
                        'refId'         => $player->refId,
                        'name'          => $player->name,
                        'gender'        => $player->gender,
                        'category'      => $invalidData['category'],
                        'balance'       => $invalidData['balance'],
                        'isYouthPlayer' => $this->hasYoungPlayer([$player]),
                    ];
                }),
            ]);
        });
    }

    /**
     * @param array|Squad[] $squads
     *
     * @return array
     */
    private function playerToCategoriesLookupMap(array $squads) : array
    {
        $lookupMap = [];
        $categories = collect($squads)->pluck('categories')->flatten();
        foreach ($categories as $category) {
            foreach ($category->players as $player) {
                $lookupMap[$player->refId][] = $category->category;
            }
        }

        return $lookupMap;
    }

    private function translateCategory($shortCategory, $gender)
    {
        if ($shortCategory === "MD") {
            if (strtoupper($gender) === "M") {
                return "MxH";
            }

            return "MxD";
        }

        return $shortCategory;
    }

    /**
     * @param Squad         $squad
     * @param array|Squad[] $squads
     *
     * @return array
     */
    private function compare(Squad $squad, array $squads) : array
    {

        /** @var Player[] $playersFromSquad */
        $players = collect($squad->categories)->flatten(1)->pluck('players')->flatten()->unique('refId');

        /** @var Player[] $playersFromHighSquads */
        $playersFromHighSquads = collect($squads)->pluck('categories')->flatten()->pluck('players')->flatten()->unique('refId');

        $playerToCategoriesLookupMap = $this->playerToCategoriesLookupMap($squads);

        $limit = 50;
        $summeryInvalid = [];
        /** @var Player $player */
        foreach ($players as $player) {
            foreach ($playersFromHighSquads as $playerHigh) {
                $invalid = [];
                // A comparison should only be made in the category or categories in which the players on the higher-ranked team participate.
                $categories = $playerToCategoriesLookupMap[$playerHigh->refId] ?? [];
                foreach ($categories as $category) {
                    $category = $this->translateCategory($category, $playerHigh->gender);
                    if ($player->gender !== $playerHigh->gender || $this->isYoungPlayer($playerHigh) || $this->isYoungPlayer($player)) {
                        continue;
                    }

                    try {
                        $belowPlayerCategoryPoint = $this->getPlayerCategoryPoint($player, $category);
                    } catch (PointNotFoundInCategoryException $categoryException) {
                        $belowPlayerCategoryPoint = null;
                    }
                    try {
                        $abovePlayerCategoryPoint = $this->getPlayerCategoryPoint($playerHigh, $category);
                    } catch (PointNotFoundInCategoryException $categoryException) {
                        $abovePlayerCategoryPoint = null;
                    }

                    // If below player is not placed in the category ranking it is marked as valid
                    if ($belowPlayerCategoryPoint === null || $abovePlayerCategoryPoint === null) {
                        $balance = 0;
                    } else {
                        $balance = ($abovePlayerCategoryPoint + $limit) - $belowPlayerCategoryPoint;
                    }

                    if ($balance < 0) {
                        $invalid[] = [
                            'balance'  => $belowPlayerCategoryPoint - $abovePlayerCategoryPoint,
                            'category' => $category,
                            'player'   => $playerHigh,
                        ];
                    }
                }
                // Only include if conflicting in all categories
                if (count($invalid) === count($categories)) {
                    $summeryCurrent = $summeryInvalid[$player->refId] ?? null;
                    if($summeryCurrent === null){
                        $summeryInvalid[$player->refId] = [
                            'player'   => $player,
                            'conflict' => $playerHigh,
                            'invalid'  => $invalid,
                        ];
                    }else{
                        $summeryCurrent['invalid'] = array_merge($invalid, $summeryCurrent['invalid']);
                        $summeryInvalid[$player->refId] = $summeryCurrent;
                    }
                }
            }
        }

        return $summeryInvalid;
    }

    public function validateSquads(array $squads) : array{
        collect($squads);
        foreach ($squads as $squad){
            $categoriesGroups = collect(collect($squad)->get('categories'))->groupBy('category');
            foreach ($categoriesGroups as $categoryName => $categoryGroup){
                foreach ($categoryGroup as $category){

                }
                $categoryGroup->map(function(Category $category) use ($categoryName) {
                    if($this->isDoubles($categoryName)){
                        return $this->getPairPoints($category->players, $categoryName);
                    }else{
                        $player = collect($category->players)->first();
                        return $this->getPlayerCategoryPoint($player, $categoryName);
                    }
                });
            }
        }
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
     * @param array|Squad[] $squads
     */
    public function validateBasicSquads(array $squads) : Collection
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
                    throw new RuntimeException('Unknown category');
                }
            }

            $entries->push([
                'index'          => $index,
                'spotsFulfilled' => $validt,
            ]);
        }

        return $entries;
    }

    /**
     *
     * @param array|Player[] $players
     */
    private function hasYoungPlayer(array $players) : bool
    {
        foreach ($players as $player) {
            foreach ($player->points as $point) {
                if ($point->vintage !== null && $point->vintage !== ''
                    && in_array(strtoupper($point->vintage),
                        ['U17', 'U19'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    private function isYoungPlayer(Player $player) : bool
    {
        return $this->hasYoungPlayer([$player]);
    }

}
