<?php
declare(strict_types = 1);


namespace FlyCompany\Stats\GraphQL\Queries;

use App\Models\Clubhouse;
use FlyCompany\Stats\Stats;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ClubhouseStats
{

    public function __construct(private Stats $stats)
    {
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed> $args        The field arguments passed by the client.
     * @param GraphQLContext       $context     Shared between all fields.
     * @param ResolveInfo          $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

        /** @var Clubhouse $clubhouse */
        $clubhouse = Clubhouse::query()->findOrFail($args['id']);

        $clubs = $clubhouse->clubs()->get();

        $players = $clubs->reduce(function ($carry, $club) {
            return $club->members()->count() + $carry;
        }, 0);

        $womenPlayers = $clubs->reduce(function ($carry, $club) {
            return $club->members()->where('gender', 'K')->count() + $carry;
        }, 0);

        $menPlayers = $clubs->reduce(function ($carry, $club) {
            return $club->members()->where('gender', 'M')->count() + $carry;
        }, 0);

        return [
            'players'      => $players,
            'womenPlayers' => $womenPlayers,
            'menPlayers'   => $menPlayers,
        ];
    }
}
