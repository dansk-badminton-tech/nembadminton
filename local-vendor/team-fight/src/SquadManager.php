<?php

declare(strict_types=1);


namespace FlyCompany\TeamFight;

use App\Models\Member;
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
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

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

    public function copySquad(SquadModel $sourceSquad, Teams $targetTeam) : SquadModel
    {
        $newSquad = $sourceSquad->replicate();
        $targetTeam->squads()->save($newSquad);

        // Copy category
        foreach ($sourceSquad->categories as $sourceCategory){
            $newCategory = $sourceCategory->replicate();
            $newSquad->categories()->save($newCategory);

            foreach ($sourceCategory->players as $sourcePlayer){
                $newPlayer = $sourcePlayer->replicate();
                $newCategory->players()->save($newPlayer);

                foreach ($sourcePlayer->points as $point){
                    $newPoint = $point->replicate();
                    $newPlayer->points()->save($newPoint);
                }
            }
        }

        return $newSquad;
    }

    public function addPlayerToSquadByRefId(string $refId, int $categoryId, string $version)
    {

        /** @var Member $member */
        $member = Member::query()
                        ->where('refId', '=', $refId)
                        ->firstOrFail();
        $squadMember = new SquadMember([
            'name'          => $member->name,
            'gender'        => $member->gender,
            'member_ref_id' => $member->refId,
            'squad_category_id' => $categoryId
        ]);
        $squadMember->save();

        foreach ($member->points()->where('version', '=', $version)->get() as $point){
            $squadMember->points()->create([
                'points' => $point->points,
                'position' => $point->position,
                'category' => $point->category,
                'squad_member_id' => $squadMember->id,
                'vintage' => $point->vintage,
                'version' => $point->version
            ]);
        }

        return $squadMember;
    }

    /**
     * @param int    $userId
     * @param string $squadId
     *
     * @return SquadModel
     */
    private function getSquadOrFail(int $userId, int $squadId): \App\Models\Squad
    {
        return \App\Models\Squad::query()->whereRelation('team', 'user_id', $userId)->where('id', '=', $squadId)->firstOrFail();
    }

    public function updatePoints(int $userId, int $squadId, ?string $version) : SquadModel{

        return DB::transaction(function() use ($userId, $squadId, $version) {
            $squad = $this->getSquadOrFail($userId, $squadId);
            $squad->fill(['version' => $version]);
            $squad->saveOrFail();

            $pointVersion = $version ?? $squad->team->version;

            /** @var SquadCategory $category */
            foreach ($squad->categories()->with('players')->get() as $category){
                foreach ($category->players as $member){
                    $member->points()->delete();
                    /** @var \App\Models\Point[] $points */
                    $points = \App\Models\Point::query()
                                   ->where('version', $pointVersion)
                                   ->whereHas('member', function (Builder $query) use ($member) {
                                       $query->where('refId', $member->member_ref_id);
                                   })->get();
                    foreach ($points as $point) {
                        $squadPoint = new SquadPoint();
                        $squadPoint->position = $point->position;
                        $squadPoint->category = $point->category;
                        $squadPoint->points = $point->points;
                        $squadPoint->vintage = $point->vintage;
                        $squadPoint->version = $point->version;
                        $squadPoint->squad_member_id = $member->id;
                        $squadPoint->saveOrFail();
                    }
                }
            }
            return $squad;
        });
    }

}
