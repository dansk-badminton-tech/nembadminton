<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\SquadMember;
use App\Models\Teams;
use App\Models\User;
use App\Policies\MemberPolicy;
use App\Policies\SquadMemberPolicy;
use App\Policies\TeamsPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        SquadMember::class => SquadMemberPolicy::class,
        Member::class => MemberPolicy::class,
        Teams::class => TeamsPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::define('admin', static function (User $user) {
            return $user->email === 'danielflynygaard@gmail.com';
        });

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        $this->registerPolicies();
    }
}
