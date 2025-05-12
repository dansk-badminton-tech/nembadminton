<?php


namespace FlyCompany\Stats;

use App\Models\Club;
use App\Models\Member;
use App\Models\Point;
use App\Models\Squad;
use App\Models\Teams;
use Carbon\Carbon;
use FlyCompany\Members\Enums\Category;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Stats
{

    public function getMetric(Metric $metric)
    {
        return match ($metric) {
            Metric::TEAMROUNDS_COUNT => $this->teamRoundsCount(),
            Metric::TEAMS_COUNT => $this->getTeamRoundsCount(),
            Metric::IMPORTED_CLUBS_COUNT => $this->getImportedClubsCount(),
        };
    }

    private function getTeamRoundsCount() : array{
        return Squad::query()
                    ->selectRaw('DATE_FORMAT(created_at,\'%Y-%m\') as date, count(*) as count')
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->get()
                    ->map(function (Squad $data) {
                        return [
                            'points'  => $data['count'],
                            'version' => Carbon::createFromFormat('Y-m',$data['date']),
                        ];
                    })->toArray();
    }

    private function getImportedClubsCount(){
        return Club::query()
                    ->selectRaw('DATE_FORMAT(created_at,\'%Y-%m\') as date, count(*) as count')
                    ->where('initialized', '=', 1)
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->get()
                    ->map(function (Club $data) {
                        return [
                            'points'  => $data['count'],
                            'version' => Carbon::createFromFormat('Y-m',$data['date']),
                        ];
                    })->toArray();
    }

    /**
     * @return array
     */
    private function teamRoundsCount() : array
    {
        return Teams::query()
                    ->selectRaw('DATE_FORMAT(created_at,\'%Y-%m\') as date, count(*) as count')
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->get()
                    ->map(function (Teams $data) {
                return [
                    'points'  => $data['count'],
                    'version' => Carbon::createFromFormat('Y-m',$data['date']),
                ];
            })->toArray();
    }

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

    /**
     * @param array    $clubIDs
     * @param Category $category
     * @param int      $limit
     * @param string   $orderBy
     * @param string[] $vintages
     *
     * @return array
     */
    public function getLowToHighestPoints(array $clubIDs, Category $category, int $limit, string $orderBy, array $vintages) : array
    {

        $clubIds = implode(',', array_filter($clubIDs, static fn($clubId) => is_int($clubId)));

        $vintageResolved = implode(", ", array_map(static fn($vintage) => DB::escape($vintage), $vintages));

        $results = DB::select("
            WITH member_points AS (
                SELECT
                    member_id,
                    points,
                    version,
                    FIRST_VALUE(points) OVER (
                        PARTITION BY member_id
                        ORDER BY version ASC
                        ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING
                    ) AS earliest_points,
                    LAST_VALUE(points) OVER (
                        PARTITION BY member_id
                        ORDER BY version ASC
                        ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING
                    ) AS latest_points
                FROM points
                WHERE category = :category
                  AND version >= :version
                  AND vintage in ($vintageResolved)
            )
            SELECT
                member_id,
                earliest_points,
                latest_points,
                (latest_points - earliest_points) AS total_increase
            FROM member_points
            WHERE member_id IN (
                SELECT member_id
                FROM club_member
                WHERE club_id IN ($clubIds)
            )
            GROUP BY member_id, earliest_points, latest_points
            ORDER BY total_increase $orderBy
            LIMIT :limit
        ", [
            'category' => $category->value,
            'version'  => BadmintonPlayerHelper::getCurrentSeasonStart(),
            'limit'    => $limit,
        ]);

        $data = [];

        foreach ($results as $result) {
            $data[] = [
                'member'         => Member::query()->find($result->member_id),
                'earliestPoints' => $result->earliest_points,
                'latestPoints'   => $result->latest_points,
                'totalIncrease'  => $result->total_increase,
            ];
        }

        return $data;
    }

}
