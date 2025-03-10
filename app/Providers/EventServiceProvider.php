<?php
declare(strict_types = 1);

namespace App\Providers;

use App\Events\CancellationCreated;
use App\Events\ClubhouseCreated;
use App\Events\InvitationCreated;
use App\Events\UserUpdate;
use App\Listeners\AddedClubConnection;
use App\Listeners\ConfirmationEmail;
use App\Listeners\EnsureClubConnection;
use App\Listeners\GrantRoleToClubhouse;
use App\Listeners\InitializeClub;
use App\Listeners\SendInvitationEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            InitializeClub::class,
            EnsureClubConnection::class
        ],
        UserUpdate::class => [
            AddedClubConnection::class
        ],
        CancellationCreated::class => [
            ConfirmationEmail::class
        ],
        InvitationCreated::class => [
            SendInvitationEmail::class
        ],
        ClubhouseCreated::class => [
            GrantRoleToClubhouse::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {

    }
}
