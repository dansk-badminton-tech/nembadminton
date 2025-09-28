<?php
declare(strict_types = 1);


namespace FlyCompany\Club\GraphQL\Queries;

use App\Models\Point;
use App\Models\User;
use FlyCompany\Club\RankingVersionUtil;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class RankingVersions
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
     * @throws \JsonException
     * @throws \Throwable
     *
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var User $user */
        $user = $context->user();

        $rankings = [];
        foreach ($user->clubhouse->clubs as $club) {
            $rankings = array_merge(RankingVersionUtil::getRankingVersionByClub($club->id), $rankings);
        }
        return array_unique($rankings);
    }

}
