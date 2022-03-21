<?php

namespace FlyCompany\BadmintonPlayerAPI;

use FlyCompany\BadmintonPlayerAPI\Commands\CacheWarmUp;
use FlyCompany\BadmintonPlayerAPI\Commands\LeagueMatch;
use FlyCompany\BadmintonPlayerAPI\Commands\LeagueMatchLineup;
use FlyCompany\BadmintonPlayerAPI\Commands\RankingPoints;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class MainServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->app->singleton(BadmintonPlayerAPI::class, function(){
            return BadmintonPlayerAPI::make(env('BADMINTONPLAYER_API_EMAIL'), env('BADMINTONPLAYER_API_PASSWORD'), Cache::store());
        });

        $this->commands([
            LeagueMatch::class,
            LeagueMatchLineup::class,
            RankingPoints::class,
            CacheWarmUp::class
        ]);
    }


}
