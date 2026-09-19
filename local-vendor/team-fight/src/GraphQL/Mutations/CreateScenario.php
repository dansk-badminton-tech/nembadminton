<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CreateScenario
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): TeamRoundScenario
    {
        $teamRoundId = (string) $args['teamRoundId'];
        $name = (string) $args['name'];

        return DB::transaction(function () use ($teamRoundId, $name) {
            /** @var TeamRound $teamRound */
            $teamRound = TeamRound::query()->findOrFail($teamRoundId);

            $scenario = TeamRoundScenario::create([
                'team_round_id' => $teamRound->id,
                'name' => $name,
                'is_official' => false,
            ]);

            $officialScenario = $teamRound->officialScenario;

            foreach ($teamRound->squads as $squad) {
                $categoriesQuery = $squad->categories();
                if ($officialScenario !== null) {
                    $categoriesQuery->where('team_round_scenario_id', $officialScenario->id);
                } else {
                    $categoriesQuery->whereNull('team_round_scenario_id');
                }

                $sourceCategories = $categoriesQuery->with(['players.points'])->get();
                if ($sourceCategories->isEmpty() && $officialScenario !== null) {
                    $sourceCategories = $squad->categories()->whereNull('team_round_scenario_id')->with(['players.points'])->get();
                }

                foreach ($sourceCategories as $sourceCategory) {
                    /** @var SquadCategory $newCategory */
                    $newCategory = $sourceCategory->replicate();
                    $newCategory->squad_id = $squad->id;
                    $newCategory->team_round_scenario_id = $scenario->id;
                    $newCategory->save();

                    foreach ($sourceCategory->players as $sourcePlayer) {
                        $newPlayer = $sourcePlayer->replicate();
                        $newCategory->players()->save($newPlayer);

                        foreach ($sourcePlayer->points as $point) {
                            $newPoint = $point->replicate();
                            $newPlayer->points()->save($newPoint);
                        }
                    }
                }
            }

            return $scenario;
        });
    }
}
