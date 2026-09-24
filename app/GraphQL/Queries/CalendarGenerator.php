<?php

namespace App\GraphQL\Queries;

use App\Models\Clubhouse;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CalendarGenerator
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $clubhouse = Clubhouse::query()->findOrFail($args['clubhouseId']);

        return [
            'clubs' => $clubhouse->clubs,
        ];
    }
}
