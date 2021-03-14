<?php


namespace FlyCompany\Scraper\GraphQL\Mutations;

use App\Models\Teams;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\TeamFight\Enricher;
use FlyCompany\TeamFight\SquadManager;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class BadmintonPlayerTeamMatchImport
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
        $clubId = $args['badmintonPlayerTeamMatch']['clubId'];
        $leagueMatchId = $args['badmintonPlayerTeamMatch']['leagueMatchId'];
        $season = $args['badmintonPlayerTeamMatch']['season'];
        $version = $args['badmintonPlayerTeamMatch']['version'];
        $side = $args['side'];

        /** @var Teams $team */
        $team = Teams::query()->where('id', $args['team'])->firstOrFail();

        $teamMatch = $this->scraper->getTeamMatch($clubId, $leagueMatchId, $season);
        foreach ($teamMatch->{$side}->squad->categories as $category) {
            $this->enricher->players($category->players, $version);
        }

        $this->squadManager->addSquads([$teamMatch->{$side}->squad], $team);

        return $teamMatch;
    }
}
