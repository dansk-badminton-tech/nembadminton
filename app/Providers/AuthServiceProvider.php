<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\SquadMember;
use App\Models\User;
use App\Policies\MemberPolicy;
use App\Policies\SquadMemberPolicy;
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
        Member::class => MemberPolicy::class
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
        $this->registerPolicies();
    }
}
