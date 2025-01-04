<?php
declare(strict_types = 1);


namespace FlyCompany\Stats\GraphQL\Queries;

use App\Models\Club;
use App\Models\Point;
use App\Models\User;
use FlyCompany\Stats\Stats;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ClubStats
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

        $clubId = $args['id'];

        /** @var User $user */
        $user = $context->user();

        /** @var Club $club */
        $club = Club::query()->findOrFail($clubId);

        $latestVersion = Point::query()->orderByDesc('version')->limit(1)->value('version');

//        $top10MenSingle = $club
//            ->members()
//            ->whereHas('points', function (Builder $builder) use ($latestVersion) {
//                $builder->where('category', 'HS')
//                        ->where('version', $latestVersion)
//                        ->orderBy('points', 'desc');
//            })
//            ->limit(10)
//            ->get();

        return [
            'players'      => $user->club->members()->count(),
            'womenPlayers' => $user->club->members()->where('gender', 'K')->count(),
            'menPlayers'   => $user->club->members()->where('gender', 'M')->count(),
        ];
    }
}
