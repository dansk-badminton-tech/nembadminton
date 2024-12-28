<?php

declare(strict_types=1);

namespace FlyCompany\Members;

use App\Models\Member;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MemberSearch
{

    /**
     * @param $root
     * @param  array  $args
     * @param  GraphQLContext  $context
     * @param  ResolveInfo  $resolveInfo
     * @return Builder
     */
    public function searchPoints($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Builder
    {
        $version = $args['version'] ?? null;
        $builder = Member::query();
        $teamId = $args['teamId'];
        $rankingList = $args['rankingList'];
        if ($version !== null) {
            $builder
                ->select(['members.*'])
                ->join('points', function(JoinClause $builder) use ($rankingList, $version) {
                    $builder->on('members.id', '=', 'points.member_id');
                    $builder->where('points.version', '=', $version);
                    $this->applyRanking($builder, $rankingList);
                })
                ->orderBy('points', 'desc');
            $builder
                ->whereHas('points', function (Builder $query) use ($rankingList, $version) {
                    $query->where('points', '!=', 0);
                    $query->where('version', $version);
                    $this->applyRanking($query, $rankingList);
                });
            $builder->notOnSquad($teamId);
        }

        return $builder;
    }

    private function applyRanking(\Illuminate\Contracts\Database\Query\Builder $builder, string $rankingList): void{
        if($rankingList === 'ALL'){
            $builder->whereIn('category', ['DS', 'DD', 'MxD', 'HS', 'HD', 'MxH']);
        }else{
            $builder->where('category', '=', $rankingList);
        }
    }
}
