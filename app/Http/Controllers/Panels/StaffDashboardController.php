<?php

namespace App\Http\Controllers\Panels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
      public function index()
    {
        return view('panels.staff.dashboard');
    }
}
