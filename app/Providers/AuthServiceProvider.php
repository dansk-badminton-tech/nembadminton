<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\SquadMember;
use App\Policies\MemberPolicy;
use App\Policies\SquadMemberPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
        $this->registerPolicies();
    }
}
