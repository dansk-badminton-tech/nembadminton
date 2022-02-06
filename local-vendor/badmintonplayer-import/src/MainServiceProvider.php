<?php

namespace FlyCompany\BadmintonPlayerImport;

use FlyCompany\BadmintonPlayerImport\Commands\PointsImporter;
use Illuminate\Support\ServiceProvider;

class MainServiceProvider extends ServiceProvider
{

    public function boot(){
        if($this->app->runningInConsole()){
            $this->commands([
                PointsImporter::class
            ]);
        }
    }

    public function register()
    {

    }

}
