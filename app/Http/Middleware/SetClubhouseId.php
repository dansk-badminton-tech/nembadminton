<?php


namespace App\Http\Middleware;

use App\Models\User;

class SetClubhouseId
{

    public function handle($request, \Closure $next){
        /** @var User $user */
        $user = auth()->user();
        if($user !== null){
            // session value set on login
            setPermissionsTeamId($user->clubhouse_id);
        }
        // other custom ways to get team_id
        /*if(!empty(auth('api')->user())){
            // `getTeamIdFromToken()` example of custom method for getting the set team_id
            setPermissionsTeamId(auth('api')->user()->getTeamIdFromToken());
        }*/

        return $next($request);
    }

}
