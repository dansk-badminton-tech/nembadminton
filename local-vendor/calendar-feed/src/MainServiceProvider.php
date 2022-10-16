<?php


namespace FlyCompany\CalendarFeed;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class MainServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
    }

}
