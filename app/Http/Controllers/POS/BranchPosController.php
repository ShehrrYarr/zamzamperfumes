<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class BranchPosController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shop = Shop::find($user->shop_id);
        abort_if(!$shop || $shop->type !== 'branch', 403);

        return view('pos.index', [
            'mode' => 'branch',
            'shop' => $shop,
            'backUrl' => route('branch.dashboard'),
        ]);
    }
}
