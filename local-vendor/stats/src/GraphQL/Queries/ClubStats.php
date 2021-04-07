<?php
declare(strict_types = 1);


namespace FlyCompany\Stats\GraphQL\Queries;

use App\Models\Point;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ClubStats
{

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

        /** @var User $user */
        $user = $context->user();

        $hd = $user->club->members()->where('gender', 'M')->whereHas('points', function (Builder $builder) {
            $builder->where('category', 'HD')->orderBy('position', 'desc');
        })->first();

        return [
            'players'        => $user->club->members()->count(),
            'womenPlayers'   => $user->club->members()->where('gender', 'K')->count(),
            'menPlayers'     => $user->club->members()->where('gender', 'M')->count(),
            'highestPlayers' => [
//                'DL' => $user->club->members()->where('gender', 'K')->whereHas('points', function(Builder $builder){
//                    $builder->whereNull('category')->orderBy('points');
//                })->first(),
//                'HL' => $user->club->members()->where('gender', 'M')->whereHas('points', function(Builder $builder){
//                    $builder->whereNull('category')->orderBy('points');
//                })->first(),
//                'HS' => $user->club->members()->where('gender', 'M')->whereHas('points', function(Builder $builder){
//                    $builder->where('category', 'HS')->orderBy('points');
//                })->first(),
//                'DS' => $user->club->members()->where('gender', 'K')->whereHas('points', function(Builder $builder){
//                    $builder->where('category', 'DS')->orderBy('points');
//                })->first(),
'HD' => $hd,
//                'DD' => $user->club->members()->where('gender', 'K')->whereHas('points', function(Builder $builder){
//                    $builder->where('category', 'DD')->orderBy('points');
//                })->first(),
//                'MxD' => $user->club->members()->where('gender', 'K')->whereHas('points', function(Builder $builder){
//                    $builder->where('category', 'MxD')->orderBy('points');
//                })->first(),
//                'MxH' => $user->club->members()->where('gender', 'M')->whereHas('points', function(Builder $builder){
//                    $builder->where('category', 'MxH')->orderBy('points');
//                })->first(),
            ],
        ];
    }
}
