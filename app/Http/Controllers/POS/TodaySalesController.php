<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Shop;
use Illuminate\Http\Request;

class TodaySalesController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop, 403);
        abort_if(!in_array($shop->type, ['main','branch'], true), 403);
        return $shop;
    }

   public function index(Request $request)
{
    $shop = $this->currentShopOrFail();

    $today = now()->toDateString();

    $sales = Sale::with(['payments.bank'])
        ->where('shop_id', $shop->id)
        ->whereDate('created_at', $today)

        // ✅ ONLY customer sales (support old rows where sale_type is NULL)
        ->where(function ($q) {
            $q->whereNull('sale_type')
              ->orWhere('sale_type', 'customer');
        })

        ->orderByDesc('id')
        ->limit(20)
        ->get();

    $count = $sales->count();
    $grand = (float) $sales->sum('grand_total');

    // payments are loaded, so we can safely compute from collection
    $counter = (float) $sales->flatMap->payments->where('method', 'counter')->sum('amount');
    $bank    = (float) $sales->flatMap->payments->where('method', 'bank')->sum('amount');

    return response()->json([
        'ok' => true,
        'summary' => [
            'count' => $count,
            'grand_total' => round($grand, 2),
            'counter_total' => round($counter, 2),
            'bank_total' => round($bank, 2),
        ],
        'sales' => $sales->map(function ($s) {
            $pay = $s->payments->first();
            return [
                'id' => $s->id,
                'time' => optional($s->created_at)->format('H:i'),
                'customer' => $s->customer_name ?: 'Walk-in',
                'total' => (float) $s->grand_total,
                'method' => $pay?->method,
                'bank' => $pay?->bank?->name,
            ];
        }),
    ]);
}
}
