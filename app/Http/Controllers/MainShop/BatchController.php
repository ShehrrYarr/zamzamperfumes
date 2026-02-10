<?php

namespace App\Http\Controllers\MainShop;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Perfume;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $mainShop = Shop::find($user->shop_id);
        abort_if(!$mainShop || $mainShop->type !== 'main', 403);

        $batches = Batch::with('perfume')
            ->where('shop_id', $mainShop->id)
            ->orderByDesc('id')
            ->get();

        return view('panels.main.batches.index', compact('batches', 'mainShop'));
    }

    public function create()
    {
        $user = auth()->user();
        $mainShop = Shop::find($user->shop_id);
        abort_if(!$mainShop || $mainShop->type !== 'main', 403);

        $perfumes = Perfume::where('is_active', true)->orderBy('name')->get();

        return view('panels.main.batches.create', compact('perfumes', 'mainShop'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $mainShop = Shop::find($user->shop_id);
        abort_if(!$mainShop || $mainShop->type !== 'main', 403);

        $data = $request->validate([
            'perfume_id' => ['required','exists:perfumes,id'],
            'batch_no'   => ['nullable','string','max:255'],
            'quantity'   => ['required','integer','min:0'],
            'cost_price' => ['nullable','numeric','min:0'],
            'sell_price' => ['nullable','numeric','min:0'],
            'mfg_date'   => ['nullable','date'],
            'exp_date'   => ['nullable','date','after_or_equal:mfg_date'],
        ]);

        DB::transaction(function () use ($data, $mainShop) {
            $max = Batch::lockForUpdate()
                ->selectRaw("MAX(CAST(barcode AS UNSIGNED)) as mx")
                ->value('mx');

            $next = ($max ? (int)$max : 0) + 1;

            $barcode = str_pad((string)$next, 5, '0', STR_PAD_LEFT);

            Batch::create([
                'perfume_id' => $data['perfume_id'],
                'shop_id'    => $mainShop->id,
                'barcode'    => $barcode,
                'batch_no'   => $data['batch_no'] ?? null,
                'quantity'   => (int)$data['quantity'],
                'cost_price' => $data['cost_price'] ?? null,
                'sell_price' => $data['sell_price'] ?? null,
                'mfg_date'   => $data['mfg_date'] ?? null,
                'exp_date'   => $data['exp_date'] ?? null,
                'is_active'  => true,
            ]);
        });

        return redirect()->route('main.batches.index')->with('success', 'Batch created with auto barcode.');
    }


    public function print(Request $request, \App\Models\Batch $batch)
{
    $user = auth()->user();
    $mainShop = \App\Models\Shop::find($user->shop_id);
    abort_if(!$mainShop || $mainShop->type !== 'main', 403);

    // IMPORTANT: main shop can only print its own batches
    abort_if((int)$batch->shop_id !== (int)$mainShop->id, 403);

    $w = (float) $request->query('w', 2.0);
    $h = (float) $request->query('h', 1.0);

    if ($w <= 0 || $w > 10) $w = 2.0;
    if ($h <= 0 || $h > 10) $h = 1.0;

    $batch->load('perfume', 'shop');

    return view('shared.batches.print', compact('batch', 'w', 'h'));
}

}
