<?php

namespace App\Providers;

use App\Models\User;
use Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
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
        // Hack to prevent unauthorized exception on null returns
        Gate::define('view', static function (?User $user, $model) {
            return $model === null;
        });
    }
}
