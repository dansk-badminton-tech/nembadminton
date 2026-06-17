<?php
declare(strict_types=1);

namespace App\Models;

use Joselfonseca\LighthouseGraphQLPassport\Models\SocialProvider as BaseSocialProvider;

/**
 * Project-local subclass so Lighthouse auto-resolves the GraphQL `SocialProvider`
 * type to a model under the configured `App\Models` namespace.
 *
 * The vendor `byOAuthToken()` method writes rows using the vendor class;
 * the `User::socialProviders()` relation reads via this class. Both share
 * the same `social_providers` table, so interop is transparent.
 */
class SocialProvider extends BaseSocialProvider
{
}
