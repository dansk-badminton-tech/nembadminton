<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Builders;

use App\Models\SquadMember;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SquadMemberSearch
{

    /**
     * @param $root
     * @param  array  $args
     * @param  GraphQLContext  $context
     * @param  ResolveInfo  $resolveInfo
     * @return Builder
     */
    public function searchBuilder($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Builder
    {
        $builder = SquadMember::query();

        $squadId = $args['squadId'];
        $builder->whereHas('category.squad', static function(Builder $builder) use ($squadId) {
            $builder->where('id', '=', $squadId);
        });

        $builder->where('name', 'like', $args['name']);

        $gender = $args['gender'] ?? null;
        if ($gender !== null){
            $builder->whereIn('gender', $gender);
        }

        return $builder;
    }

}
