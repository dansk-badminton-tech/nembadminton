<?php


namespace FlyCompany\Scraper\GraphQL\Queries;

use App\Models\Teams;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\TeamFight\Enricher;
use FlyCompany\TeamFight\SquadManager;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class BadmintonPlayerTeams
{

    /**
     * @var BadmintonPlayer
     */
    private BadmintonPlayer $scraper;


    public function __construct(BadmintonPlayer $scraper)
    {
        $this->scraper = $scraper;
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

        return $this->scraper->getClubTeams($season, $clubId);
    }
}
