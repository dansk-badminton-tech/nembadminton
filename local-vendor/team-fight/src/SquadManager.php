<?php

declare(strict_types=1);


namespace FlyCompany\TeamFight;

use App\Models\Squad as SquadModel;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\Teams;
use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Point;
use FlyCompany\TeamFight\Models\Squad;

class SquadManager
{

    /**
     * @param  Squad[]  $squads
     * @param  Teams  $team
     *
     * @throws \Throwable
     */
    public function addSquads(array $squads, Teams $team): void
    {
        foreach ($squads as $squadInput) {
            $squad = SquadModel::query()->find($squadInput->id);
            if($squad === null){
                $squad = new SquadModel(['playerLimit' => $squadInput->playerLimit, 'league' => $squadInput->league]);
                $squad->forceFill(['teams_id' => $team->id]);
                $squad->saveOrFail();
            }else{
                $squad->updateOrFail(['playerLimit' => $squadInput->playerLimit, 'league' => $squadInput->league]);
            }
            $squad->categories()->delete();
            $this->createCategories($squadInput->categories, $squad);
        }
    }

    /**
     * @param  Category[]  $categories
     * @param  SquadModel  $squad
     *
     * @throws \Throwable
     */
    private function createCategories(array $categories, SquadModel $squad): void
    {
        foreach ($categories as $category) {
            $categoryModel = new SquadCategory(['name' => $category->name, 'category' => $category->category]);
            $categoryModel->forceFill(['squad_id' => $squad->id]);
            $categoryModel->saveOrFail();
            $this->createPlayers($category->players, $categoryModel);
        }
    }

    /**
     * @param  Player[]  $players
     * @param  SquadCategory  $category
     */
    private function createPlayers(array $players, SquadCategory $category): void
    {
        foreach ($players as $player) {
            /** @var SquadMember $member */
            $member = SquadMember::query()->create(
                [
                    'gender' => $player->gender,
                    'name' => $player->name,
                    'member_ref_id' => $player->refId,
                    'squad_category_id' => $category->id
                ]
            );
            $this->createPoints($player->points, $member);
        }
    }

    /**
     * @param  Point[]  $points
     * @param  SquadMember  $member
     */
    private function createPoints(array $points, SquadMember $member): void
    {
        $values = [];
        foreach ($points as $point) {
            $values[] = [
                'points' => $point->points,
                'category' => $point->category,
                'position' => $point->position,
                'vintage' => $point->vintage,
                'squad_member_id' => $member->id
            ];
        }
        SquadPoint::query()->insert($values);
    }

}
