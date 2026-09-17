<?php

namespace App\GraphQL\Mutations;

use App\Models\Member;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SetMemberInactiveOverride
{
    /**
     * @throws \GraphQL\Error\Error
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Member
    {
        $member = Member::find($args['id']);
        if ($member === null) {
            throw new Error('Member not found.');
        }

        $mode = $args['mode'];

        $updates = ['override_inactive' => $mode];
        if ($mode === 'FORCE_INACTIVE') {
            $updates['inactive'] = true;
        } elseif ($mode === 'FORCE_ACTIVE') {
            $updates['inactive'] = false;
        }

        $member->update($updates);

        return $member->refresh();
    }
}
