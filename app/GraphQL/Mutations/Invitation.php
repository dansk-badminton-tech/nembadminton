<?php


namespace App\GraphQL\Mutations;

use App\Models\Invitation as InvitationModel;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Invitation
{

    /**
     * @throws \Throwable
     */
    public function acceptInvitation($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo){
        /** @var InvitationModel $invitation */
        $invitation = InvitationModel::query()->where('token','=',$args['token'])->where('status', '=', 'pending')->firstOrFail();
        $invitation->accept();
        $invitation->saveOrFail();

        $this->connectClubhouse($context->user(), $invitation);

        $this->assignRole($context->user(), $invitation);

        return $invitation;
    }

    public function declineInvitation($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo){
        /** @var InvitationModel $invitation */
        $invitation = InvitationModel::query()->where('token','=',$args['token'])->where('status', '=', 'pending')->firstOrFail();
        $invitation->decline();
        $invitation->saveOrFail();

        $this->connectClubhouse($context->user(), $invitation);

        $this->assignRole($context->user(), $invitation);

        return $invitation;
    }

    private function assignRole(User $user, InvitationModel $invitation) : void
    {
        $user->assignRole($invitation->role);
    }

    private function connectClubhouse(User $user, InvitationModel $invitation) : void
    {
        $user->clubhouse()->associate($invitation->clubhouse);
        $user->save();
    }

}
