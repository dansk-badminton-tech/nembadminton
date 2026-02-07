<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\TeamActivityLog;
use App\Models\TeamReceivers;
use App\Models\TeamRound;
use App\Models\Teams;
use App\Models\User;
use App\Policies\MemberPolicy;
use App\Policies\SquadCategoryPolicy;
use App\Policies\SquadMemberPolicy;
use App\Policies\SquadPointPolicy;
use App\Policies\SquadPolicy;
use App\Policies\TeamActivityLogPolicy;
use App\Policies\TeamReceiversPolicy;
use App\Policies\TeamRoundPolicy;
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
        Teams::class => TeamsPolicy::class,
        TeamReceivers::class => TeamReceiversPolicy::class,
        TeamActivityLog::class => TeamActivityLogPolicy::class,
        SquadCategory::class => SquadCategoryPolicy::class,
        Squad::class => SquadPolicy::class,
        SquadPoint::class => SquadPointPolicy::class,
        TeamRound::class => TeamRoundPolicy::class,
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
