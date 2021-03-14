<?php


namespace FlyCompany\Members;

use App\Models\Member;
use App\Models\Point;
use Carbon\Carbon;

class PointsManager
{

    /**
     * @param string $name
     * @param int $points
     * @param int $position
     * @param Carbon $version
     * @param string|null $category
     * @param string $vintage
     */
    public function addPointsByName(string $name, int $points, int $position, Carbon $version, ?string $category, $vintage = 'SEN'): void
    {
        /** @var Member $member */
        $member = Member::query()->where('name', $name)->firstOrFail();

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
