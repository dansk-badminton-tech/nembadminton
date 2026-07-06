<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Error\Error;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Spatie\Permission\Models\Role;

class SetPrimaryRole
{
    /**
     * @throws \GraphQL\Error\Error
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): User
    {
        /** @var User $user */
        $user = $context->user();
        $roleId = (int) $args['roleId'];

        $role = Role::find($roleId);
        if ($role === null) {
            throw new Error('Role not found.');
        }

        setPermissionsTeamId($user->clubhouse_id);

        $assignedRoleIds = $user->roles()->pluck('id')->all();
        if (!in_array($roleId, $assignedRoleIds, true)) {
            throw new Error('You do not have this role.');
        }

        $user->primary_role_id = $roleId;
        $user->save();

        return $user->refresh()->load('primaryRole');
    }
}
