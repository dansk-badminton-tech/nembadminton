<?php


namespace FlyCompany\Stats\GraphQL\Queries;

use FlyCompany\Stats\Metric;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Stats
{

    public function __construct(private \FlyCompany\Stats\Stats $stats)
    {
    }

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
        return $this->stats->getMetric(Metric::from( $args['metric']));
    }

}

