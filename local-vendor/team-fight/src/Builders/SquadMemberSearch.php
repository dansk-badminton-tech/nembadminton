<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Builders;

use App\Models\SquadMember;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SquadMemberSearch
{
    public function searchBuilder($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Builder
    {
        $builder = SquadMember::query();

        $squadId = $args['squadId'];
        $scenarioId = $args['scenarioId'] ?? null;
        $builder->whereHas('category', static function (Builder $builder) use ($squadId, $scenarioId) {
            $builder->where('squad_id', $squadId)
                ->where('team_round_scenario_id', $scenarioId);
        });

        $builder->where('name', 'like', $args['name']);

        $gender = $args['gender'] ?? null;
        if ($gender !== null) {
            $builder->whereIn('gender', $gender);
        }

        return $builder;
    }
}
