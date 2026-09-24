<?php

namespace FlyCompany\Scraper\GraphQL\Queries;

use DiDom\Exceptions\InvalidSelectorException;
use FlyCompany\TeamFight\TeamValidator;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class BadmintonPlayerValidate
{
    private TeamValidator $teamValidator;

    public function __construct(TeamValidator $teamValidator)
    {
        $this->teamValidator = $teamValidator;
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param  array<string, mixed>  $args  The field arguments passed by the client.
     * @param  GraphQLContext  $context  Shared between all fields.
     * @param  ResolveInfo  $resolveInfo  Metadata for advanced query resolution.
     * @return mixed
     *
     * @throws \JsonException
     * @throws \Throwable
     * @throws InvalidSelectorException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

        $this->teamValidator->validateSquads();
    }
}
