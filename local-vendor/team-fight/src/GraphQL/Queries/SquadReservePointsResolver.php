<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Member;
use App\Models\Point;
use App\Models\SquadReserve;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Collection;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * SquadReserve.reservePoints: the reserve's ranking points at the Squad's ranking version, so the editor can check a
 * reserve who isn't placed (§43 stk. 3). Empty for a User of another Clubhouse.
 */
class SquadReservePointsResolver
{
    /**
     * @param  array<string, mixed>  $args
     * @return Collection<int, Point>
     */
    public function __invoke(SquadReserve $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Collection
    {
        $squad = $root->squad;
        $teamRound = $squad->teamRound;
        $user = $context->user();
        if (! $user instanceof User || (int) $user->clubhouse_id !== (int) $teamRound->clubhouse_id) {
            return new Collection;
        }

        return Point::query()
            ->whereIn('member_id', Member::query()->where('refId', $root->reserve_ref_id)->select('id'))
            ->whereDate('version', $squad->version ?? $teamRound->version)
            ->orderBy('category')
            ->get();
    }
}
