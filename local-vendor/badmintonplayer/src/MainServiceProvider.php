<?php

namespace FlyCompany\BadmintonPlayer;

use FlyCompany\BadmintonPlayer\Commands\PointsImporter;
use FlyCompany\BadmintonPlayer\Commands\Test;
use Illuminate\Support\ServiceProvider;

class MainServiceProvider extends ServiceProvider
{

    public function boot(){
        if($this->app->runningInConsole()){
            $this->commands([
                PointsImporter::class,
                Test::class
            ]);
        }
    }

    public function register()
    {

    }

}
