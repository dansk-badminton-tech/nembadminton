<?php
declare(strict_types = 1);


namespace App\GraphQL\Queries;

use App\Models\Teams;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class TeamsByBadmintonId
{

    /**
     * @param                      $rootValue
     * @param array<string, mixed> $args
     * @param GraphQLContext       $context
     * @param ResolveInfo          $resolveInfo
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var User $user */
        $user = $context->user();

        return Teams::query()->whereHas('squads.categories.players', function (Builder $builder) use ($user) {
            $builder->where('member_ref_id', $user->player_id);
        })->get();
    }
}
