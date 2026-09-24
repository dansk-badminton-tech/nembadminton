<?php

namespace FlyCompany\Scraper\GraphQL\Queries;

use FlyCompany\Scraper\BadmintonPlayer;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ImportClubOptions
{
    private BadmintonPlayer $badmintonPlayer;

    public function __construct(BadmintonPlayer $badmintonPlayer)
    {
        $this->badmintonPlayer = $badmintonPlayer;
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param  array<string, mixed>  $args  The field arguments passed by the client.
     * @param  GraphQLContext  $context  Shared between all fields.
     * @param  ResolveInfo  $resolveInfo  Metadata for advanced query resolution.
     * @return mixed
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return $this->badmintonPlayer->getClubs();
    }
}
