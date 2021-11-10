<?php

declare(strict_types=1);

namespace FlyCompany\Members;

use App\Models\Member;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MemberSearch
{

    public function searchBuilder($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $version = $args['version'] ?? null;
        $builder = Member::query();
        if ($version !== null) {
            $builder->select(['members.*'])
                ->join('points', 'members.id', '=', 'points.member_id')
                ->where('points.position', '!=', 0)
                ->orderBy('points.points', 'desc');
            if (!Arr::has($args, 'hasCancellation')) {
                $builder->whereDoesntHave('cancellations', static function (Builder $builder) use ($args) {
                    $builder->where('teamId', '=', $args['onTeamSquad']);
                });
            }
            if (Arr::has($args, 'onTeamSquad')) {
                $builder->whereDoesntHave('squadMember.category.squad', function (Builder $builder) use ($args) {
                    $builder->where('teams_id', '=', $args['onTeamSquad']);
                });
            }
            if (Arr::has($args, 'rankingList')) {
                $this->applyRankingList($builder, $args['rankingList'], $version);
            }
        }
        return $builder;
    }

    private function applyRankingList(Builder $builder, string $rankingList, string $version)
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
