<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Squad;
use App\Models\User;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SquadMatchKind
{
    /** @param array<string, mixed> $args */
    public function __invoke(Squad $squad, array $args, GraphQLContext $context): ?string
    {
        /** @var User|null $user */
        $user = $context->user();

        return $user?->clubhouse_id === $squad->teamRound->clubhouse_id
            ? $squad->match_kind?->value
            : null;
    }
}
