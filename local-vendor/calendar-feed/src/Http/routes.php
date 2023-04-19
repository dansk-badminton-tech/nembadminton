<?php

use FlyCompany\CalendarFeed\Http\Controllers\ICalController;
use Illuminate\Support\Facades\Route;

Route::get('/team/{clubId}/ical', [ICalController::class, 'ical']);
Route::get('/team/{clubId}/ical-classic', [ICalController::class, 'icalClassic']);
