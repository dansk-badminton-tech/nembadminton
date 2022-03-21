<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\GraphQL\Query;

use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class RankingVersions
{

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed> $args        The field arguments passed by the client.
     * @param GraphQLContext       $context     Shared between all fields.
     * @param ResolveInfo          $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return [
            Carbon::now()->setDay(1),
            Carbon::now()->subMonth()->setDay(1)
        ];
    }

}
