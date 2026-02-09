<?php

namespace App\Http\Controllers\Panels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MainDashboardController extends Controller
{
    public function index()
    {
        return view('panels.main.dashboard');
    }
}
