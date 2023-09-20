<?php


namespace FlyCompany\Club;

use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class RankingVersionUtil
{

    public static function getRankingVersionByClub(int $clubId) : array
    {
//        $expires = Carbon::now()->addDay();
//        /** @var Collection $rankingVersions */
        $rankingVersions = static::fetchVersions($clubId);

//            Cache::remember(self::getCacheKey($clubId), $expires, static function () use ($clubId) {
//            return static::fetchVersions($clubId);
//        });

//        if ($rankingVersions->isEmpty()){
//            Cache::forget(self::getCacheKey($clubId));
//            $rankingVersions = static::fetchVersions($clubId);
//        }

        return array_reverse(Arr::sort($rankingVersions->pluck('version')));
    }

    private static function fetchVersions(int $clubId) : Collection|array
    {
        return Point::select('version')
                    ->distinct()
                    ->join('members', 'points.member_id', '=', 'members.id')
                    ->join('club_member', 'members.id', '=', 'club_member.member_id')
                    ->join('clubs', function($join) use ($clubId) {
                        $join->on('clubs.id', '=', 'club_member.club_id')
                             ->where('clubs.id', '=', $clubId);
                    })->get();
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
