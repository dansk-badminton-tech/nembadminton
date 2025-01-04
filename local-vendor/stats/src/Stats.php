<?php


namespace FlyCompany\Stats;

use App\Models\Point;
use Carbon\Carbon;
use FlyCompany\Members\Enums\Category;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Stats
{

    /**
     * @param string   $memberId
     * @param Category $category
     *
     * @return Collection
     */
    public function getCategoryPoint(string $memberId, Category $category) : Collection
    {
        return Point::query()
                    ->where('category', $category)
                    ->whereHas('member', function (Builder $query) use ($memberId) {
                        $query->where('id', $memberId);
                    })
                    ->where('version', '>=', BadmintonPlayerHelper::getCurrentSeasonStart()->subYear())
                    ->orderBy('version')->get();
    }

}
