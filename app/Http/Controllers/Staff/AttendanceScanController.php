<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class AttendanceScanController extends Controller
{
    public function scan(string $token, Request $request)
    {
        $user = auth()->user();

        // Only staff can use scan
        abort_if($user->role !== 'staff', 403);

        $shop = Shop::where('qr_token', $token)->first();
        abort_if(!$shop, 404);

        // Staff can ONLY scan their own assigned shop
        abort_if((int)$user->shop_id !== (int)$shop->id, 403);

        // If everything is ok -> redirect to attendance page with verified token
        return redirect()->route('staff.attendance', ['token' => $token]);
    }
}
