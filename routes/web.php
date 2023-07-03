<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('notifications/{id}/dismiss', [NotificationController::class, 'dismiss']);
Route::get('forgot-password-finish', 'Spa2Controller@index')->name('password.reset');
Route::get('php-info', function () {
    if (app()->environment('local')) {
        phpinfo();
    }
});
Route::get('/app/v1/{any}', 'SpaController@index')->where('any', '.*');
Route::get('/app/{any}', 'Spa2Controller@index')->where('any', '.*');
Route::get('/{any}', 'MarketingSpaController@index')->where('any', '.*');
