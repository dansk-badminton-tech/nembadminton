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
                ->whereNull('points.category')
                ->where(
                    'points.version',
                    '=',
                    $version
                )->orderBy('points.points', 'desc');
            if(!Arr::has($args, 'hasCancellation')){
                $builder->whereDoesntHave('cancellations');
            }
            if(Arr::has($args, 'onTeamSquad')){
                $builder->whereDoesntHave('squadMember.category.squad', function(Builder $builder) use ($args) {
                    $builder->where('teams_id', '=', $args['onTeamSquad']);
                });
            }
        }
        return $builder;
    }

}
