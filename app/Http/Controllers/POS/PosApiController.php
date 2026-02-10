<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Shop;
use Illuminate\Http\Request;

class PosApiController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        $user = auth()->user();
        $shop = Shop::find($user->shop_id);
        abort_if(!$shop, 403);

        // only main or branch shops can use POS endpoints
        abort_if(!in_array($shop->type, ['main','branch'], true), 403);

        return $shop;
    }

    private function cartKey(int $shopId): string
    {
        return "pos_cart_shop_{$shopId}";
    }

    public function items(Request $request)
    {
        $shop = $this->currentShopOrFail();
        $q = trim((string)$request->query('q', ''));
        $barcode = trim((string)$request->query('barcode', ''));

        $query = Batch::with('perfume')
            ->where('shop_id', $shop->id)
            ->where('is_active', true)
            ->where('quantity', '>', 0);

        if ($barcode !== '') {
            $query->where('barcode', $barcode);
        } elseif ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('barcode', 'like', "%{$q}%")
                    ->orWhereHas('perfume', function ($p) use ($q) {
                        $p->where('name', 'like', "%{$q}%")
                          ->orWhere('brand', 'like', "%{$q}%")
                          ->orWhere('sku', 'like', "%{$q}%");
                    });
            });
        }

        $items = $query->orderByDesc('id')->limit(30)->get()->map(function ($b) {
            return [
                'batch_id' => $b->id,
                'barcode' => $b->barcode,
                'perfume' => $b->perfume?->name ?? '—',
                'brand' => $b->perfume?->brand,
                'qty' => (int)$b->quantity,
                'sell_price' => $b->sell_price !== null ? (float)$b->sell_price : null,
            ];
        });

        return response()->json(['ok' => true, 'items' => $items]);
    }

    public function cart(Request $request)
    {
        $shop = $this->currentShopOrFail();
        $cart = session()->get($this->cartKey($shop->id), []);

        return response()->json(['ok' => true, 'cart' => array_values($cart)]);
    }

    public function add(Request $request)
    {
        $shop = $this->currentShopOrFail();

        $data = $request->validate([
            'batch_id' => ['required','integer'],
            'qty' => ['nullable','integer','min:1'],
        ]);

        $qtyToAdd = (int)($data['qty'] ?? 1);

        $batch = Batch::with('perfume')
            ->where('id', $data['batch_id'])
            ->where('shop_id', $shop->id)
            ->where('is_active', true)
            ->firstOrFail();

        abort_if($batch->quantity <= 0, 422, 'Out of stock');

        $key = $this->cartKey($shop->id);
        $cart = session()->get($key, []);

        $id = (string)$batch->id;
        $currentQty = isset($cart[$id]) ? (int)$cart[$id]['qty'] : 0;
        $newQty = $currentQty + $qtyToAdd;

        // cannot exceed available qty
        if ($newQty > (int)$batch->quantity) {
            $newQty = (int)$batch->quantity;
        }

        $cart[$id] = [
            'batch_id' => $batch->id,
            'barcode' => $batch->barcode,
            'perfume' => $batch->perfume?->name ?? '—',
            'qty' => $newQty,
            'price' => $batch->sell_price !== null ? (float)$batch->sell_price : 0.0,
            'available' => (int)$batch->quantity,
        ];

        session()->put($key, $cart);

        return response()->json(['ok' => true, 'cart' => array_values($cart)]);
    }

    public function update(Request $request)
    {
        $shop = $this->currentShopOrFail();

        $data = $request->validate([
            'batch_id' => ['required','integer'],
            'qty' => ['required','integer','min:1'],
        ]);

        $batch = Batch::where('id', $data['batch_id'])
            ->where('shop_id', $shop->id)
            ->firstOrFail();

        $key = $this->cartKey($shop->id);
        $cart = session()->get($key, []);
        $id = (string)$batch->id;

        abort_if(!isset($cart[$id]), 404);

        $newQty = (int)$data['qty'];
        if ($newQty > (int)$batch->quantity) {
            $newQty = (int)$batch->quantity;
        }

        $cart[$id]['qty'] = $newQty;
        $cart[$id]['available'] = (int)$batch->quantity;

        session()->put($key, $cart);

        return response()->json(['ok' => true, 'cart' => array_values($cart)]);
    }

    public function remove(Request $request)
    {
        $shop = $this->currentShopOrFail();

        $data = $request->validate([
            'batch_id' => ['required','integer'],
        ]);

        $key = $this->cartKey($shop->id);
        $cart = session()->get($key, []);
        $id = (string)$data['batch_id'];

        unset($cart[$id]);
        session()->put($key, $cart);

        return response()->json(['ok' => true, 'cart' => array_values($cart)]);
    }

    public function banks()
{
    $shop = $this->currentShopOrFail();

    $banks = \App\Models\Bank::where('shop_id', $shop->id)
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id','name','account_number','iban']);

    return response()->json(['ok' => true, 'banks' => $banks]);
}

}
