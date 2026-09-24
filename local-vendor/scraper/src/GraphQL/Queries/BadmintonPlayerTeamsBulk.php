<?php

namespace FlyCompany\Scraper\GraphQL\Queries;

use DiDom\Exceptions\InvalidSelectorException;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\TeamFight\Enricher;
use FlyCompany\TeamFight\SquadManager;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class BadmintonPlayerTeamsBulk
{
    private BadmintonPlayer $scraper;

    private Enricher $enricher;

    private SquadManager $squadManager;

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
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

        $results = [];
        foreach ($args['input'] as $arg) {
            $clubId = $arg['clubId'];
            $season = $arg['season'];
            $results = array_merge($this->scraper->getClubTeams($season, $clubId), $results);
        }

        return $results;
    }
}
