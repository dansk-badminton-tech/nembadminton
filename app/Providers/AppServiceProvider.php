<?php

namespace App\Providers;

use App\Models\User;
use Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Loops\LoopsClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LoopsClient::class, function () {
            return new LoopsClient(env('LOOPS_API_KEY'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());
        // oauth_clients has integer ids, and PASSPORT_CLIENT_ID refers to one of them
        Passport::$clientUuids = false;
        // Hack to prevent unauthorized exception on null returns
        Gate::define('view', static function (?User $user, $model) {
            return $model === null;
        });
    }
}
