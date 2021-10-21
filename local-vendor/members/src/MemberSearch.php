<?php

declare(strict_types=1);

namespace FlyCompany\Members;

use App\Models\Member;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MemberSearch
{

    public function searchBuilder($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $version = $args['version'] ?? null;
        $builder = Member::query();
        if ($version !== null) {
            $builder->select(['members.*'])
                ->join('points', 'members.id', '=', 'points.member_id')
                ->whereNull('points.category')
                ->where(
                    'points.version',
                    '=',
                    $version
                )->orderBy('points.points', 'desc');

            if (!$args['includeCancellations']) {
                $builder->whereDoesntHave('cancellations', static function (Builder $builder) use ($args) {
                    $builder->where('teamId', '=', $args['hasClubs']['value']);
                });
            }
        }
        return $builder;
    }

}
