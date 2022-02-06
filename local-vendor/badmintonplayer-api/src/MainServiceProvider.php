<?php

namespace FlyCompany\BadmintonPlayerAPI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class MainServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->app->singleton(BadmintonPlayerAPI::class, function(){
            return BadmintonPlayerAPI::make(env('BADMINTONPLAYER_API_EMAIL'), env('BADMINTONPLAYER_API_PASSWORD'), Cache::store());
        });
    }


}
