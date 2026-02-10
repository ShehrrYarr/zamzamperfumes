<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Shop;

class ReceiptController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop, 403);
        abort_if(!in_array($shop->type, ['main','branch'], true), 403);
        return $shop;
    }

    public function show(Sale $sale)
    {
        $shop = $this->currentShopOrFail();

        // Ensure user can only view receipts for their own shop
        abort_if($sale->shop_id !== $shop->id, 403);

        $sale->load(['items', 'payments.bank', 'user', 'shop']);

        return view('pos.receipt', compact('sale', 'shop'));
    }
}
