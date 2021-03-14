<?php
declare(strict_types = 1);


namespace App\GraphQL\Mutations;

use App\Models\Point;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\Teams;
use App\Models\User;
use App\Notifications\TeamUpdated;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use FlyCompany\TeamFight\SquadManager;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Symfony\Component\Serializer\Serializer;

/**
 * Class UpdateTeams
 *
 * @package App\GraphQL\Mutation
 */
class UpdateTeams
{

    /**
     * @var SquadManager
     */
    private SquadManager $squadManager;

    public function __construct(SquadManager $squadManager)
    {
        $this->serializer = SerializerHelper::getSerializer();
        $this->squadManager = $squadManager;
    }

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     */
    public function notify($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : void
    {
        $team = $args['id'];
        $members = SquadMember::query()->whereHas('category.squad.team', function (Builder $builder) use ($team) {
            $builder->where('id', $team);
        })->with('user')->get();
        $users = $members->pluck('user')->unique('id')->filter(function ($value) {
            return $value !== null;
        });

        /** @var Teams $team */
        $team = Teams::query()->findOrFail($team);
        /** @var User[] $users */
        foreach ($users as $user) {
            $user->notify(new TeamUpdated($team));
        }
    }

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     */
    public function updatePoints($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : bool
    {
        $teamId = $args['id'];
        $version = $args['version'];

        /** @var SquadPoint[] $squadPoints */
        $squadPoints = SquadPoint::query()->with('member')->whereHas('member.category.squad.team', function (Builder $query) use ($context, $teamId) {
            $query->where('id', $teamId);
            $query->where('user_id', $context->user()->getAuthIdentifier());
        })->get();

        foreach ($squadPoints as $squadPoint) {
            $point = Point::query()
                          ->where('version', $version)
                          ->where('category', $squadPoint->category)
                          ->whereHas('member', function (Builder $query) use ($squadPoint) {
                              $query->where('refId', $squadPoint->member->member_ref_id);
                          })->first();
            if ($point !== null) {
                $squadPoint->points = $point->points;
                $squadPoint->position = $point->position;
                $squadPoint->saveOrFail();
            }
        }

        return true;
    }

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return Teams
     * @throws \Throwable
     */
    public function updateTeams($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : Teams
    {
        /** @var Teams $team */
        $team = Teams::query()
                     ->where('user_id', $context->user()->getAuthIdentifier())
                     ->where('id', $args['id'])->firstOrFail();
        $team->fill(Arr::only($args, ['name', 'game_date', 'version']));
        $team->saveOrFail();

        // Clear all squads
        $team->squads()->delete();

        $squads = $args['squads'] ?? [];
        /** @var Squad[] $squads */
        $squads = $this->serializer->denormalize($squads, Squad::class . '[]');
        $this->squadManager->addSquads($squads, $team);

        return $team;
    }
}
