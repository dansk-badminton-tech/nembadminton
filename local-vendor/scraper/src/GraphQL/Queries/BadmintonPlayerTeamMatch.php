<?php


namespace FlyCompany\Scraper\GraphQL\Queries;

use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\TeamFight\Enricher;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Psr\SimpleCache\InvalidArgumentException;

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
     * @throws InvalidArgumentException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $leagueMatchId = $args['leagueMatchId'];
        $season = $args['season'];
        $version = $args['version'] ?? null;

        $teamMatch = $this->scraper->getTeamMatch($leagueMatchId, $season);

        if($version !== null){
            foreach ($teamMatch->guest->squad->categories as $category) {
                $this->enricher->players($category->players, $version);
            }
            foreach ($teamMatch->home->squad->categories as $category) {
                $this->enricher->players($category->players, $version);
            }
        }

        return $teamMatch;
    }
}
