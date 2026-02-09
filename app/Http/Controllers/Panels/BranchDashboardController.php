<?php

namespace App\Http\Controllers\Panels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchDashboardController extends Controller
{
     public function index()
    {
        return view('panels.branch.dashboard');
    }
}
