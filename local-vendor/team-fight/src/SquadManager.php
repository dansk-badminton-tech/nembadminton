<?php
declare(strict_types = 1);


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
     * @param Squad[] $squads
     * @param Teams   $team
     *
     * @throws \Throwable
     */
    public function addSquads(array $squads, Teams $team) : void
    {
        foreach ($squads as $squadInput) {
            $squad = new SquadModel(['playerLimit' => $squadInput->playerLimit]);
            $squad->forceFill(['teams_id' => $team->id]);
            $squad->saveOrFail();
            $this->createCategories($squadInput->categories, $squad);
        }
    }

    /**
     * @param Category[] $categories
     * @param SquadModel $squad
     *
     * @throws \Throwable
     */
    private function createCategories(array $categories, SquadModel $squad) : void
    {
        foreach ($categories as $category) {
            $categoryModel = new SquadCategory(['name' => $category->name, 'category' => $category->category]);
            $categoryModel->forceFill(['squad_id' => $squad->id]);
            $categoryModel->saveOrFail();
            $this->createPlayers($category->players, $categoryModel);
        }
    }

    /**
     * @param Player[]      $players
     * @param SquadCategory $category
     */
    private function createPlayers(array $players, SquadCategory $category) : void
    {
        foreach ($players as $player) {
            /** @var SquadMember $member */
            $member = SquadMember::query()->updateOrCreate(
                ['gender' => $player->gender, 'name' => $player->name, 'member_ref_id' => $player->refId, 'squad_category_id' => $category->id],
                ['member_ref_id' => $player->refId, 'squad_category_id' => $category->id]
            );
            $this->createPoints($player->points, $member);
        }
    }

    /**
     * @param Point[]     $points
     * @param SquadMember $member
     */
    private function createPoints(array $points, SquadMember $member) : void
    {
        foreach ($points as $point) {
            SquadPoint::query()->updateOrCreate(
                ['points' => $point->points, 'category' => $point->category, 'position' => $point->position, 'squad_member_id' => $member->id],
                ['squad_member_id' => $member->id]
            );
        }
    }

}
