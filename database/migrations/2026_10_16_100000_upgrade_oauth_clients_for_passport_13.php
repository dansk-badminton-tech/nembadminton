<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;

/**
 * Passport 13: move oauth_clients to the new schema and hash client secrets.
 *
 * With integer client ids, the old schema makes Passport reject token requests
 * from users whose id matches a client id (laravel/passport#1912).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->nullableMorphs('owner', after: 'user_id');

            $table->after('provider', function (Blueprint $table) {
                $table->text('redirect_uris')->nullable();
                $table->text('grant_types')->nullable();
            });
        });

        foreach (Passport::client()->cursor() as $client) {
            Model::withoutTimestamps(fn () => $client->forceFill([
                'owner_id' => $client->user_id,
                'owner_type' => $client->user_id
                    ? config('auth.providers.'.($client->provider ?: config('auth.guards.api.provider')).'.model')
                    : null,
                'redirect_uris' => $client->redirect_uris,
                // Not the inferred grant types: they include client_credentials, which makes
                // Passport reject tokens of the user whose id equals the client id.
                'grant_types' => $client->password_client ? ['password', 'refresh_token'] : $client->grant_types,
            ])->save());
        }

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'redirect', 'personal_access_client', 'password_client']);

            $table->text('redirect_uris')->nullable(false)->change();
            $table->text('grant_types')->nullable(false)->change();
        });

        // Passport 13 always compares against hashed secrets; skips already-hashed ones.
        Artisan::call('passport:hash', ['--force' => true]);
    }

    /**
     * Restores the old columns. Client secrets stay hashed.
     */
    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
            $table->text('redirect')->nullable()->after('provider');
            $table->boolean('personal_access_client')->default(false)->after('redirect');
            $table->boolean('password_client')->default(false)->after('personal_access_client');
        });

        foreach (Passport::client()->cursor() as $client) {
            $grantTypes = $client->grant_types;

            Model::withoutTimestamps(fn () => $client->forceFill([
                'user_id' => $client->owner_id,
                'redirect' => implode(',', $client->redirect_uris),
                'personal_access_client' => in_array('personal_access', $grantTypes, true),
                'password_client' => in_array('password', $grantTypes, true),
            ])->save());
        }

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropMorphs('owner');
            $table->dropColumn(['redirect_uris', 'grant_types']);
        });
    }
};
