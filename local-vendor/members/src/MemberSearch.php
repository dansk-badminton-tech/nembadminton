<?php

declare(strict_types=1);

namespace FlyCompany\Members;

use App\Models\Member;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\WhereConditions\WhereConditionsHandler;
use Nuwave\Lighthouse\WhereConditions\WhereConditionsServiceProvider;

class MemberSearch
{

    /**
     * @param $root
     * @param  array  $args
     * @param  GraphQLContext  $context
     * @param  ResolveInfo  $resolveInfo
     * @return Builder
     */
    public function searchPoints($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Builder
    {
        $version = $args['version'] ?? null;
        $builder = Member::query();
        $teamId = $args['teamId'];
        if ($version !== null) {
            $builder->select(['members.*'])
                ->join('points', 'members.id', '=', 'points.member_id')
                ->where('points.points', '!=', 0)
                ->orderBy('points.points', 'desc');
            $builder->notOnSquad($teamId);
            if (Arr::has($args, 'rankingList')) {
                $this->applyRankingList($builder, $args['rankingList'], $version);
            }
        }

        return $builder;
    }

    private function applyRankingList(Builder $builder, string $rankingList, Carbon $version)
    {
        if ($rankingList === 'ALL_LEVEL') {
            $builder->whereNull('points.category')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
        if ($rankingList === 'MEN_LEVEL') {
            $builder->where('members.gender', '=', 'M');
            $builder->whereNull('points.category')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
        if ($rankingList === 'WOMEN_LEVEL') {
            $builder->where('members.gender', '=', 'K');
            $builder->whereNull('points.category')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
        if ($rankingList === 'WOMEN_SINGLE') {
            $builder->where('points.category', '=', 'DS')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
        if ($rankingList === 'WOMENS_DOUBLE') {
            $builder->where('points.category', '=', 'DD')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
        if ($rankingList === 'WOMEN_MIX') {
            $builder->where('points.category', '=', 'MxD')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
        if ($rankingList === 'MEN_SINGLE') {
            $builder->where('points.category', '=', 'HS')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
        if ($rankingList === 'MENS_DOUBLE') {
            $builder->where('points.category', '=', 'HD')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
        if ($rankingList === 'MEN_MIX') {
            $builder->where('points.category', '=', 'MxH')
                ->where(
                    'points.version',
                    '=',
                    $version
                );
        }
    }

}
