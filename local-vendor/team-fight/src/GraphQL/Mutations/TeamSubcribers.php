<?php

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\SquadMember;
use App\Models\Teams;
use App\Models\User;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class TeamSubcribers
{

    /**
     * @param                $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $team = Teams::query()
            ->with('squads.categories.players.user')
            ->where('id', $args['id'])
            ->get();

        $players = $team
            ->pluck('squads.*.categories.*.players');

        return [
            'amount' => $players->count(),
            'players' => $players->map(fn(SquadMember $player) => [
                'name' => $player->name,
                'refId' => $player->member_ref_id
            ])
        ];
    }

}
