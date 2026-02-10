<?php

namespace App\Http\Controllers\Panels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $shop = $user->shop_id ? \App\Models\Shop::find($user->shop_id) : null;

    return view('panels.staff.dashboard', compact('user', 'shop'));
}

}
