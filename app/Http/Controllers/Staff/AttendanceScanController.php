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
    abort_if($user->role !== 'staff', 403);

    $shop = \App\Models\Shop::where('qr_token', $token)->first();
    abort_if(!$shop, 404);

    // staff can only scan their own shop
    abort_if((int)$user->shop_id !== (int)$shop->id, 403);

    $slot = $request->query('slot');
    $sig  = $request->query('sig');

    abort_if(!$slot || !$sig, 403);

    $slot = (int) $slot;
    $currentSlot = (int) floor(now()->timestamp / 300);

    // ✅ allow current slot OR previous slot (grace for clock skew)
    abort_if(!in_array($slot, [$currentSlot, $currentSlot - 1], true), 403);

    $expected = hash_hmac('sha256', $token.'|'.$slot, config('app.key'));
    abort_if(!hash_equals($expected, $sig), 403);

    return redirect()->route('staff.attendance', ['token' => $token]);
}


}
