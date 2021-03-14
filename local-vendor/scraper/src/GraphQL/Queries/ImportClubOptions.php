<?php


namespace FlyCompany\Scraper\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use GuzzleHttp\Client;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ImportClubOptions
{

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed>                                $args        The field arguments passed by the client.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context     Shared between all fields.
     * @param \GraphQL\Type\Definition\ResolveInfo                $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $client = new Client();
        $response = $client->get('http://www.badmintonpeople.dk/sportsresults/components/clubcomponents/clublistclientscript.aspx?unionid=1');
        $body = $response->getBody()->getContents();
        $needle = 'var SportsResultsTeamList =';
        $pos = strpos($body, $needle);
        $pos += strlen($needle);

        $clubsStr = rtrim(trim(substr($body, $pos)), ';');
        $clubsStr = str_replace("'", '"', $clubsStr);
        $clubs = json_decode($clubsStr, true, 512, JSON_THROW_ON_ERROR);

        $responseJson = [];
        foreach ($clubs as $clubPair) {

            $clubName = str_replace("–", "-", $clubPair[0]);
            $responseJson[] = [
                'id'   => $clubPair[1],
                'name' => $clubName,
            ];
        }

        return $responseJson;
    }
}
