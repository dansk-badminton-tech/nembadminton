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

Route::redirect('/', '/app/home-redirect');

// Public privacy policy (standalone page, accessible without login). Must be
// declared before the SPA catch-all below so it is not swallowed by '/{any}'.
Route::view('/privatlivspolitik', 'privacy')->name('privacy-policy');
Route::view('/privacy-policy', 'privacy');

Route::get('/{any}', 'Spa2Controller@index')->where('any', '.*');

