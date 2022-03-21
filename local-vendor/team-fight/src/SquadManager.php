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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SquadManager
{

    /**
     * @param  Squad[]  $squads
     * @param  Teams  $team
     *
     * @throws \Throwable
     */
    public function addOrUpdateSquads(array $squads, Teams $team): void
    {
        foreach ($squads as $index => $squadInput) {
            $squad = SquadModel::query()->whereHas('team', function(Builder $builder) use ($team){
                $builder->where('id', $team->id);
            })->find($squadInput->id);
            if($squad === null){
                $squad = new SquadModel(['playerLimit' => $squadInput->playerLimit, 'league' => $squadInput->league, 'order' => $index]);
                $squad->forceFill(['teams_id' => $team->id]);
                $squad->saveOrFail();
            }else{
                $squad->updateOrFail(['playerLimit' => $squadInput->playerLimit, 'league' => $squadInput->league, 'order' => $index]);
            }
            $squad->categories()->delete();
            $this->createCategories($squadInput->categories, $squad);
        }
    }

    /**
     * @param  Squad[]  $squads
     * @param  Teams  $team
     *
     * @throws \Throwable
     */
    public function removeDeletedSquads(array $squads, Teams $team): void
    {
        $shouldExistsIds = array_map('intval', Arr::pluck($squads, 'id'));
        foreach ($team->squads as $squad){
            if(!in_array($squad->id, $shouldExistsIds, true)){
                $squad->deleteOrFail();
            }
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
