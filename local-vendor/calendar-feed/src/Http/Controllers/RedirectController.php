<?php


namespace FlyCompany\CalendarFeed\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RedirectController extends Controller
{

    public function to(Request $request) : \Illuminate\Http\RedirectResponse
    {
        $redirect = $request->query('to');

        return redirect()->away($redirect);
    }

}
