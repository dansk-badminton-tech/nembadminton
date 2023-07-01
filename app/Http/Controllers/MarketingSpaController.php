<?php
declare(strict_types = 1);

namespace App\Http\Controllers;

class MarketingSpaController extends Controller
{
    public function index()
    {
        return view('marketing');
    }
}
