<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use App\Models\Member;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Point;
use Illuminate\Database\Eloquent\Collection;

class Enricher
{

    /**
     * @param Player[] $players
     * @param string   $version
     */
    public function players(array $players, string $version)
    {
        foreach ($players as $player) {
            /** @var Member $member */
            $member = Member::query()->where('name', $player->name)->first();
            if ($member === null) {
                continue;
            }
            $player->refId = $member->refId;
            $player->gender = $member->gender;
            /** @var \App\Models\Point[]|Collection $points */
            $points = $member->points()->where('version', $version)->get();
            foreach ($points as $pointModel) {
                $point = new Point();
                $point->points = $pointModel->points;
                $point->position = $pointModel->position;
                $point->category = $pointModel->category;
                $point->version = $pointModel->version;
                $player->points[] = $point;
            }
        }
    }

}
