<?php

namespace App\Http\Controllers\MainShop;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Shop;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function mainShopOrFail(): Shop
    {
        $user = auth()->user();
        $shop = Shop::find($user->shop_id);
        abort_if(!$shop || $shop->type !== 'main', 403);
        return $shop;
    }

    public function index(Request $request)
    {
        $mainShop = $this->mainShopOrFail();

        $q = trim((string)$request->query('q', ''));       // barcode/perfume search
        $onlyInStock = (bool)$request->query('in_stock', 1);

        $batchesQuery = Batch::with('perfume')
            ->where('shop_id', $mainShop->id);

        if ($onlyInStock) {
            $batchesQuery->where('quantity', '>', 0);
        }

        if ($q !== '') {
            $batchesQuery->where(function ($qq) use ($q) {
                $qq->where('barcode', 'like', "%{$q}%")
                   ->orWhereHas('perfume', function ($p) use ($q) {
                       $p->where('name', 'like', "%{$q}%")
                         ->orWhere('brand', 'like', "%{$q}%")
                         ->orWhere('sku', 'like', "%{$q}%");
                   });
            });
        }

        $batches = $batchesQuery->orderByDesc('id')->get();

        return view('panels.main.inventory.index', compact('batches', 'q', 'onlyInStock'));
    }
}
