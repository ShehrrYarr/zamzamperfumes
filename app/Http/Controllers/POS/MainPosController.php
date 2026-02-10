<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class MainPosController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shop = Shop::find($user->shop_id);
        abort_if(!$shop || $shop->type !== 'main', 403);

        return view('pos.index', [
            'mode' => 'main',
            'shop' => $shop,
            'backUrl' => route('main.dashboard'),
        ]);
    }
}
