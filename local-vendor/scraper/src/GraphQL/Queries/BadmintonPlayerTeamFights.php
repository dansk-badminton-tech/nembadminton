<?php


namespace FlyCompany\Scraper\GraphQL\Queries;

use App\Models\Teams;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\TeamFight\Enricher;
use FlyCompany\TeamFight\SquadManager;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class BadmintonPlayerTeamFights
{

    /**
     * @var BadmintonPlayer
     */
    private BadmintonPlayer $scraper;

    /**
     * @var Enricher
     */
    private Enricher $enricher;

    /**
     * @var SquadManager
     */
    private SquadManager $squadManager;

    public function __construct(BadmintonPlayer $scraper, Enricher $enricher, SquadManager $squadManager)
    {
        $this->scraper = $scraper;
        $this->enricher = $enricher;
        $this->squadManager = $squadManager;
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed>                                $args        The field arguments passed by the client.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context     Shared between all fields.
     * @param \GraphQL\Type\Definition\ResolveInfo                $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     * @throws \JsonException
     * @throws \Throwable
     *
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $clubId = $args['clubId'];
        $season = $args['season'];
        $ageGroupId = $args['ageGroupId'];
        $leagueGroupId = $args['leagueGroupId'];
        $clubName = $args['clubName'];

        return $this->scraper->getTeamFights($season, $clubId, $ageGroupId, $leagueGroupId, $clubName);
    }
}
