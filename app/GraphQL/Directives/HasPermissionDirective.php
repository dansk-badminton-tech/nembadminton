<?php declare(strict_types=1);

namespace App\GraphQL\Directives;

use App\Models\User;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class HasPermissionDirective extends BaseDirective implements FieldMiddleware
{
    // TODO implement the directive https://lighthouse-php.com/master/custom-directives/getting-started.html

    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
directive @hasPermission(
  """
  The name of the role authorized users need to have.
  """
  name: String!
) on FIELD_DEFINITION
GRAPHQL;
    }

    /**
     * Wrap around the final field resolver.
     *
     * @param  \Nuwave\Lighthouse\Schema\Values\FieldValue  $fieldValue
     */
    public function handleField(FieldValue $fieldValue): void
    {
        // If you have any work to do that does not require the resolver arguments, do it here.
        // This code is executed only once per field, whereas the resolver can be called often.

        $fieldValue->wrapResolver(fn (callable $resolver) => function (mixed $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($resolver) {
            // Do something before the resolver, e.g. validate $args, check authentication

            $name = $this->directiveArgValue('name')
                            // Throw in case of an invalid schema definition to remind the developer
                            ?? throw new DefinitionException("Missing argument 'name' for directive '@hasPermission'.");

            /** @var User $user */
            $user = $context->user();

            if(!$user->hasPermissionTo($name)){
                throw new AuthorizationException();
            }

            return $resolver($root, $args, $context, $resolveInfo);
        });
    }
}
