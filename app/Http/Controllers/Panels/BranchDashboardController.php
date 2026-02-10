<?php

namespace App\Http\Controllers\Panels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchDashboardController extends Controller
{
     public function index()
{
    $user = auth()->user();
    $branchId = $user->shop_id;

    $staffCount = \App\Models\User::where('shop_id', $branchId)
        ->where('role', 'staff')
        ->count();

    $activeStaffCount = \App\Models\User::where('shop_id', $branchId)
        ->where('role', 'staff')
        ->where('is_active', true)
        ->count();

    $branch = \App\Models\Shop::find($branchId);

    return view('panels.branch.dashboard', compact('staffCount', 'activeStaffCount', 'branch'));
}

}
