<?php

declare(strict_types=1);

namespace FlyCompany\Club\GraphQL\Queries;

use DiDom\Exceptions\InvalidSelectorException;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Psr\SimpleCache\InvalidArgumentException;

class RankingVersionsBP
{
    private BadmintonPlayer $scraper;

    public function __construct(BadmintonPlayer $scraper)
    {
        $this->scraper = $scraper;
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
     * @throws InvalidArgumentException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return BadmintonPlayerHelper::filterToRankingMonths(
            $this->scraper->getVersions(BadmintonPlayerHelper::getCurrentSeason())
        )->reverse();

    }
}
