<?php

namespace FlyCompany\BadmintonPlayer;

use FlyCompany\BadmintonPlayer\Commands\ClubImporter;
use FlyCompany\BadmintonPlayer\Commands\MembersImporter;
use FlyCompany\BadmintonPlayer\Commands\PointsImporter;
use FlyCompany\BadmintonPlayer\Commands\Test;
use FlyCompany\BadmintonPlayer\Commands\UpdateAllClubs;
use Illuminate\Support\ServiceProvider;

class MainServiceProvider extends ServiceProvider
{

    public function boot(){
        if($this->app->runningInConsole()){
            $this->commands([
                PointsImporter::class,
                MembersImporter::class,
                UpdateAllClubs::class,
                ClubImporter::class,
                Test::class
            ]);
        }
    }

    public function register()
    {

    }

}
