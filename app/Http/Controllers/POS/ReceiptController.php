<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Shop;

class ReceiptController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        abort_if(!auth()->check(), 403);

        $shopId = (int) auth()->user()->shop_id;
        abort_if(!$shopId, 403);

        $shop = Shop::find($shopId);
        abort_if(!$shop, 403);

        abort_if(!in_array($shop->type, ['main','branch'], true), 403);

        return $shop;
    }

    // NOTE: $sale is the ID from route (no implicit binding)
    public function show($sale)
    {
        $shop = $this->currentShopOrFail();

        $saleId = (int) $sale;

        // Ensure user can only view receipts for their own shop
        $sale = Sale::query()
            ->where('id', $saleId)
            ->where('shop_id', $shop->id)
            ->with(['items', 'payments.bank', 'user', 'shop'])
            ->firstOrFail();

        return view('pos.receipt', compact('sale', 'shop'));
    }
}
