<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\Role;
use App\Models\User;
use GraphQL\Error\UserError;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final readonly class Membership
{
    /**
     * Return a value for the field.
     *
     * @param  null  $root Always null, since this field has no parent.
     * @param  array{}  $args The field arguments passed by the client.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Shared between all fields.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Metadata for advanced query resolution.
     * @return mixed The result of resolving the field, matching what was promised in the schema
     */
    public function delete(null $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): mixed
    {
        /** @var User $user */
        $user = User::query()->findOrFail($args['userId']);

        /** @var User $loggedInUser */
        $loggedInUser = $context->user();
        if($user->id === $loggedInUser->getAuthIdentifier()){
            throw new UserError('You cannot delete yourself');
        }

        if($user->clubhouse->id !== (int)$args['clubhouseId'] || $loggedInUser->clubhouse->id !== (int)$args['clubhouseId']){
            throw new UserError('You cannot delete someone elses membership');
        }

        $user->clubhouses()->detach();
        $user->clubhouse()->dissociate();
        $user->save();

        return true;
    }

    public function updateRoles(null $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): User
    {
        /** @var User $target */
        $target = User::query()->findOrFail($args['userId']);

        /** @var User $actor */
        $actor = $context->user();

        if ($target->id === $actor->getAuthIdentifier()) {
            throw new UserError('You cannot edit your own roles');
        }

        if ($target->clubhouse->id !== $actor->clubhouse->id) {
            throw new UserError('You cannot edit someone elses membership');
        }

        if (!$actor->hasRole(Role::CLUB_ADMIN->value)) {
            throw new UserError('Only club admins can edit roles');
        }

        $roleEnums = array_map(fn(string $r) => Role::from($r), $args['roles']);
        $target->syncRoles(array_map(fn(Role $r) => $r->value, $roleEnums));

        $target->refresh();
        $newRoleIds = $target->roles()->pluck('roles.id')->all();

        if ($target->primary_role_id === null && !empty($newRoleIds)) {
            $target->primary_role_id = $newRoleIds[0];
            $target->save();
        } elseif ($target->primary_role_id !== null && !in_array($target->primary_role_id, $newRoleIds, true)) {
            $target->primary_role_id = !empty($newRoleIds) ? $newRoleIds[0] : null;
            $target->save();
        }

        return $target->refresh()->load(['roles', 'primaryRole']);
    }

    public function resetPlayerId(null $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): User
    {
        /** @var User $target */
        $target = User::query()->findOrFail($args['userId']);

        /** @var User $actor */
        $actor = $context->user();

        if ($target->id === $actor->getAuthIdentifier()) {
            throw new UserError('You cannot reset your own player_id');
        }

        if ($target->clubhouse->id !== $actor->clubhouse->id) {
            throw new UserError('You cannot edit someone elses membership');
        }

        if (!$actor->hasAnyRole(Role::CLUB_ADMIN->value, Role::COACH->value)) {
            throw new UserError('Only club admins and coaches can reset player_id');
        }

        $target->player_id = null;
        $target->save();

        return $target->refresh()->load(['roles', 'primaryRole']);
    }
}
