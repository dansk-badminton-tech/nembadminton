<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

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
}
