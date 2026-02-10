<?php

namespace App\Http\Controllers\Panels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MainDashboardController extends Controller
{
   public function index()
{
    $mainShopUser = auth()->user();
    $mainShop = \App\Models\Shop::find($mainShopUser->shop_id);

    $branchesCount = \App\Models\Shop::where('type', 'branch')->count();

    $mainStaffCount = \App\Models\User::where('shop_id', $mainShopUser->shop_id)
        ->where('role', 'staff')
        ->count();

    return view('panels.main.dashboard', compact('mainShop', 'branchesCount', 'mainStaffCount'));
}

}
