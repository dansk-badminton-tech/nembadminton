<?php


namespace FlyCompany\Club;

use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class RankingVersionUtil
{

    public static function getRankingVersionByClub(int $clubId) : array
    {
        $expires = Carbon::now()->addDay();
        $rankingVersions = Cache::remember(self::getCacheKey($clubId), $expires, static function () use ($clubId) {
            return Point::query()->whereHas('member.clubs', function (Builder $builder) use ($clubId) {
                $builder->where('id', $clubId);
            })->distinct()->get(['version']);
        });

        return array_reverse(Arr::sort($rankingVersions->pluck('version')));
    }

    public static function updateRankingVersionCache(int $clubId) : void
    {
        Cache::forget(self::getCacheKey($clubId));
        self::getRankingVersionByClub($clubId);
    }

    /**
     * @param int $clubId
     *
     * @return string
     */
    public static function getCacheKey(int $clubId) : string
    {
        return "ranking-versions-{$clubId}";
    }

}
