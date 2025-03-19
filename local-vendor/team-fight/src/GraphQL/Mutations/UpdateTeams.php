<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\Point;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\Teams;
use App\Models\User;
use App\Notifications\TeamUpdated;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use FlyCompany\TeamFight\SquadManager;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
    public function updatePointsOnAllSquadsInTeam($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : Teams
    {
        $teamId = $args['id'];
        $version = $args['version'];

        return DB::transaction(function() use ($teamId, $version, $context) {
            $team = $this->getTeamOrFail($context, $teamId);
            $team->fill(['version' => $version]);
            $team->saveOrFail();

            foreach ($team->squads as $squad){
                /** @var SquadMember $player */
                $versionCurrent = $squad->version ?? $version;
                /** @var SquadCategory $category */
                foreach ($squad->categories()->with('players')->get() as $category){
                    foreach ($category->players as $member){
                        $member->points()->delete();
                        /** @var Point[] $points */
                        $points = Point::query()
                                       ->where('version', $versionCurrent)
                                       ->whereHas('member', function (Builder $query) use ($member) {
                                           $query->where('refId', $member->member_ref_id);
                                       })->get();
                        foreach ($points as $point) {
                            $squadPoint = new SquadPoint();
                            $squadPoint->position = $point->position;
                            $squadPoint->category = $point->category;
                            $squadPoint->points = $point->points;
                            $squadPoint->vintage = $point->vintage;
                            $squadPoint->version = $point->version;
                            $squadPoint->squad_member_id = $member->id;
                            $squadPoint->saveOrFail();
                        }
                    }
                }
            }
            return $team;
        });
    }

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return \App\Models\Squad
     */
    public function updatePointsOnSquad($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : \App\Models\Squad
    {
        $squadId = (int)$args['id'];
        $version = $args['version'];

        return $this->squadManager->updatePoints($squadId, $version);
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
        return DB::transaction(function() use ($args, $context) {
            $team = $this->getTeamOrFail($context, $args['id']);
            $team->fill(Arr::only($args, ['name', 'game_date', 'version']));
            $team->saveOrFail();

            $squads = $args['squads'];
            /** @var Squad[] $squads */
            $squads = $this->serializer->denormalize($squads, Squad::class . '[]');
            $this->squadManager->removeDeletedSquads($squads, $team);
            $this->squadManager->addOrUpdateSquads($squads, $team);

            return $this->getTeamOrFail($context, $args['id']);
        });
    }

    /**
     * @param  GraphQLContext  $context
     * @param  mixed  $teamId
     * @return Teams
     */
    private function getTeamOrFail(GraphQLContext $context, string $teamId): Teams
    {
        return Teams::query()
            ->where('clubhouse_id', $context->user()->clubhouse_id)
            ->where('id', $teamId)->lockForUpdate()->firstOrFail();
    }
}
