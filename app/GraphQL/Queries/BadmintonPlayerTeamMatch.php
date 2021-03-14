<?php


namespace App\GraphQL\Queries;

use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\TeamFight\Enricher;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class BadmintonPlayerTeamMatch
{

    /**
     * @var BadmintonPlayer
     */
    private BadmintonPlayer $scraper;

    /**
     * @var Enricher
     */
    private Enricher $enricher;

    public function __construct(BadmintonPlayer $scraper, Enricher $enricher)
    {
        $this->scraper = $scraper;
        $this->enricher = $enricher;
    }

    /**
     * Return a value for the field.
     *
     * @param null                                                $root        Always null, since this field has no parent.
     * @param array<string, mixed>                                $args        The field arguments passed by the client.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context     Shared between all fields.
     * @param \GraphQL\Type\Definition\ResolveInfo                $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     * @throws \JsonException
     *
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $clubId = $args['clubId'];
        $leagueMatchId = $args['leagueMatchId'];
        $season = $args['season'];
        $version = $args['version'];

        $teamMatch = $this->scraper->getTeamMatch($clubId, $leagueMatchId, $season);
        foreach ($teamMatch->guest->squad->categories as $category) {
            $this->enricher->players($category->players, $version);
        }
        foreach ($teamMatch->home->squad->categories as $category) {
            $this->enricher->players($category->players, $version);
        }

        return $teamMatch;
    }
}
