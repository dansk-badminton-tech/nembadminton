<?php
declare(strict_types = 1);

namespace App\Providers;

use App\Events\UserUpdate;
use App\Listeners\AddedClubConnection;
use App\Listeners\EnsureClubConnection;
use App\Listeners\InitializeClub;
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
