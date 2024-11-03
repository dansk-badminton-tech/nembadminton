<?php


namespace FlyCompany\Members;

use App\Models\Member;
use App\Models\Point;
use Carbon\Carbon;

class PointsManager
{

    /**
     * @param Member      $member
     * @param int         $points
     * @param int         $position
     * @param Carbon      $version
     * @param string|null $category
     * @param string      $vintage
     */
    public function addPointsByMember(Member $member, int $points, int $position, Carbon $version, ?string $category, string $vintage = 'SEN') : void
    {
        Point::query()->updateOrCreate([
            'category'  => $category,
            'version'   => $version,
            'member_id' => $member->id,
        ], [
            'points'   => $points,
            //'position' => $position,
            'cll'      => null,
            'vintage'  => $vintage,
            'clh'      => null,
        ]);
    }

    /**
     * @param string      $name
     * @param int         $points
     * @param int         $position
     * @param Carbon      $version
     * @param string|null $category
     * @param string      $vintage
     */
    public function addPointsByName(string $name, int $points, int $position, Carbon $version, ?string $category, string $vintage = 'SEN') : void
    {
        /** @var Member $member */
        $member = Member::query()->where('name', $name)->firstOrFail();

        Point::query()->where([
            'category'  => $category,
            'version'   => $version,
            'member_id' => $member->id,
        ])->update([
            'points'    => $points,
            'position'  => $position,
            'cll'       => null,
            'vintage'   => $vintage,
            'clh'       => null,
        ]);
    }

    /**
     * @param string      $refId
     * @param int         $points
     * @param int         $position
     * @param Carbon      $version
     * @param string|null $category
     * @param string      $vintage
     */
    public function addPointsByRefId(string $refId, int $points, int $position, Carbon $version, ?string $category, string $vintage = 'SEN') : void
    {
        /** @var Member $member */
        $member = Member::query()->where('refId', $refId)->firstOrFail();

        Point::query()->updateOrCreate([
            'category'  => $category,
            'version'   => $version,
            'member_id' => $member->id,
        ],[
            'points'    => $points,
            'position'  => $position,
            'cll'       => null,
            'vintage'   => $vintage,
            'clh'       => null,
        ]);
    }

}
