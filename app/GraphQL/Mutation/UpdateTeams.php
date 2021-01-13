<?php
declare(strict_types = 1);


namespace App\GraphQL\Mutation;

use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\Teams;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdateTeams
 *
 * @package App\GraphQL\Mutation
 */
class UpdateTeams
{

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     */
    public function updateTeams($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var Teams $team */
        $team = Teams::query()
                     ->where('user_id', $context->user()->getAuthIdentifier())
                     ->where('id', $args['id'])->firstOrFail();
        $team->fill(Arr::only($args, ['name', 'game_date']));
        $team->saveOrFail();

        // Clear all squads
        $team->squads()->delete();

        $squads = $args['squads'] ?? [];
        foreach ($squads as $squadInput) {
            $squad = new Squad(Arr::only($squadInput, ['playerLimit']));
            $squad->forceFill(['teams_id' => $team->id]);
            $squad->saveOrFail();
            $categories = $squadInput['categories'] ?? [];
            foreach ($categories as $categoryInput) {
                $category = new SquadCategory(Arr::only($categoryInput, ['category', 'name']));
                $category->forceFill(['squad_id' => $squad->id]);
                $category->saveOrFail();
                $players = $categoryInput['players'] ?? [];
                foreach ($players as $player) {
                    $member = SquadMember::query()->updateOrCreate(
                        array_merge(Arr::only($player, ['gender', 'name']), ['member_ref_id' => $player['refId'] ?? null, 'squad_category_id' => $category->id]),
                        ['member_ref_id' => $player['refId'], 'squad_category_id' => $category->id]
                    );
                    $points = $player['points'] ?? [];
                    foreach ($points as $point) {
                        SquadPoint::query()->updateOrCreate(
                            ['points' => $point['points'], 'category' => $point['category'], 'position' => $point['position'], 'squad_member_id' => $member->id],
                            ['squad_member_id' => $member->id]
                        );
                    }
                }
            }
        }

        return $team;
    }

}
