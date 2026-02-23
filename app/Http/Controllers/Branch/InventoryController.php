<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Shop;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function branchShopOrFail(): Shop
    {
        $user = auth()->user();
        $shop = Shop::find($user->shop_id);
        abort_if(!$shop || $shop->type !== 'branch', 403);
        return $shop;
    }

    public function index()
{
    $branch = $this->branchShopOrFail();

    $batches = Batch::with('perfume')
        ->where('shop_id', $branch->id)
        ->orderByDesc('id')
        ->get();

    // ✅ Totals
    $totalQty = $batches->sum('quantity');

    $totalCost = $batches->sum(function ($b) {
        return (float)$b->quantity * (float)($b->cost_price ?? 0);
    });

    $totalSell = $batches->sum(function ($b) {
        return (float)$b->quantity * (float)($b->sell_price ?? 0);
    });

    return view('panels.branch.inventory.index', compact(
        'batches',
        'branch',
        'totalQty',
        'totalCost',
        'totalSell'
    ));
}



    public function print(Request $request, Batch $batch)
    {
        $branch = $this->branchShopOrFail();

        // prevent printing other branches’ batches
        abort_if((int)$batch->shop_id !== (int)$branch->id, 403);

        $w = (float) $request->query('w', 2.0);
        $h = (float) $request->query('h', 1.0);

        if ($w <= 0 || $w > 10) $w = 2.0;
        if ($h <= 0 || $h > 10) $h = 1.0;

        $batch->load('perfume', 'shop');

        return view('shared.batches.print', compact('batch', 'w', 'h'));
    }
}
