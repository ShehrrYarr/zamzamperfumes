<?php

namespace App\Http\Controllers;

use App\Models\SaleReturn;
use App\Models\Shop;
use Illuminate\Http\Request;

class ReturnsController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop, 403);
        abort_if(!in_array($shop->type, ['main','branch'], true), 403);
        return $shop;
    }

    private function buildQuery(Request $request, int $shopId)
    {
        $q = SaleReturn::with(['sale', 'user', 'bank', 'items.saleItem'])
            ->where('shop_id', $shopId)
            ->orderByDesc('id');

        if ($request->filled('sale_id')) {
            $q->where('sale_id', (int)$request->sale_id);
        }

        if ($request->filled('method')) {
            $q->where('method', $request->method);
        }

        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('cashier')) {
            $cashier = trim($request->cashier);
            $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$cashier}%"));
        }

        if ($request->filled('customer')) {
            $customer = trim($request->customer);
            $q->whereHas('sale', fn($sq) =>
                $sq->where('customer_name', 'like', "%{$customer}%")
                   ->orWhere('customer_phone', 'like', "%{$customer}%")
            );
        }

        return $q;
    }

    public function mainIndex(Request $request)
    {
        $shop = $this->currentShopOrFail();
        abort_if($shop->type !== 'main', 403);

        $returns = $this->buildQuery($request, $shop->id)->paginate(20)->withQueryString();

        return view('panels.main.returns.index', compact('returns', 'shop'));
    }

    public function branchIndex(Request $request)
    {
        $shop = $this->currentShopOrFail();
        abort_if($shop->type !== 'branch', 403);

        $returns = $this->buildQuery($request, $shop->id)->paginate(20)->withQueryString();

        return view('panels.branch.returns.index', compact('returns', 'shop'));
    }
}
