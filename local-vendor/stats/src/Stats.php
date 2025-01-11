<?php


namespace FlyCompany\Stats;

use App\Models\Member;
use App\Models\Point;
use FlyCompany\Members\Enums\Category;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    public function getLowToHighestPoints(array $clubIDs, Category $category, int $limit, string $orderBy) : array
    {
        $clubIds = implode(',', $clubIDs);

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
            'version' => BadmintonPlayerHelper::getCurrentSeasonStart(),
            'limit' => $limit
        ]);

        $data = [];

        foreach ($results as $result) {
            $data[] = [
                'member' => Member::query()->find($result->member_id),
                'earliestPoints' => $result->earliest_points,
                'latestPoints' => $result->latest_points,
                'totalIncrease' => $result->total_increase,
            ];
        }

        return $data;
    }

}
