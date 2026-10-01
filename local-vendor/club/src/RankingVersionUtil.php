<?php

namespace FlyCompany\Club;

use App\Models\Point;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class RankingVersionUtil
{
    public static function getRankingVersionByClub(int $clubId): array
    {
        $rankingVersions = static::fetchVersions($clubId);

        return array_reverse(Arr::sort($rankingVersions->pluck('version')));
    }

    /**
     * Badminton Danmark sometimes re-publishes a month's ranking list under a new version date,
     * which leaves two versions of the same month. The newest one is the version the importer
     * wrote most recently.
     *
     * @param  int[]  $clubIds
     * @return string[] newest version of each month, latest month first
     */
    public static function getNewestRankingVersionsByClubs(array $clubIds): array
    {
        $versions = self::lastUpdatedVersionsQuery()
            ->join('club_member', 'points.member_id', '=', 'club_member.member_id')
            ->whereIn('club_member.club_id', $clubIds)
            ->get();

        return self::newestPerMonth($versions);
    }

    public static function getLatestRankingVersion(): ?string
    {
        return self::newestPerMonth(self::lastUpdatedVersionsQuery()->get())[0] ?? null;
    }

    private static function lastUpdatedVersionsQuery(): Builder
    {
        return DB::table('points')
            ->select('points.version')
            ->selectRaw('MAX(points.updated_at) as last_updated')
            ->groupBy('points.version');
    }

    /**
     * @param  SupportCollection<int, \stdClass>  $versions  rows of version and last_updated
     * @return string[]
     */
    private static function newestPerMonth(SupportCollection $versions): array
    {
        return $versions
            ->groupBy(static fn (\stdClass $row): string => substr((string) $row->version, 0, 7))
            ->map(static fn (SupportCollection $sameMonth): string => (string) $sameMonth
                ->sort(static fn (\stdClass $a, \stdClass $b): int => [$b->last_updated, $b->version] <=> [$a->last_updated, $a->version])
                ->first()
                ->version)
            ->sortKeysDesc()
            ->values()
            ->all();
    }

    private static function fetchVersions(int $clubId): Collection|array
    {
        return Point::select('version')
            ->distinct()
            ->join('members', 'points.member_id', '=', 'members.id')
            ->join('club_member', 'members.id', '=', 'club_member.member_id')
            ->join('clubs', function ($join) use ($clubId) {
                $join->on('clubs.id', '=', 'club_member.club_id')
                    ->where('clubs.id', '=', $clubId);
            })->get();
    }
}
