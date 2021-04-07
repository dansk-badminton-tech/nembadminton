<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight\GraphQL\Mutations;

use FlyCompany\TeamFight\TeamValidator;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Validate
{

    /**
     * @var TeamValidator
     */
    private TeamValidator $teamValidator;

    public function __construct(TeamValidator $teamValidator)
    {
        $this->teamValidator = $teamValidator;
    }

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return array
     */
    public function validate($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : array
    {
        return $this->teamValidator->validateSquads($args['squads']);
    }

}
