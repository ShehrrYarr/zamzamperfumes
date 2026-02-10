<?php

namespace App\Http\Controllers\Admin;

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
        $mainShop = Shop::where('type', 'main')->first();
        abort_if(!$mainShop, 404, 'Main shop not found.');

        $batches = Batch::with('perfume')
            ->where('shop_id', $mainShop->id)
            ->orderByDesc('id')
            ->get();

        return view('admin.batches.index', compact('batches', 'mainShop'));
    }

    public function create()
    {
        $mainShop = Shop::where('type', 'main')->first();
        abort_if(!$mainShop, 404, 'Main shop not found.');

        $perfumes = Perfume::where('is_active', true)->orderBy('name')->get();

        return view('admin.batches.create', compact('perfumes', 'mainShop'));
    }

    public function store(Request $request)
    {
        $mainShop = Shop::where('type', 'main')->first();
        abort_if(!$mainShop, 404, 'Main shop not found.');

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
            // lock rows so two creates don't get same max
            $max = Batch::lockForUpdate()
                ->selectRaw("MAX(CAST(barcode AS UNSIGNED)) as mx")
                ->value('mx');

            $next = ($max ? (int)$max : 0) + 1;

            $barcode = str_pad((string)$next, 5, '0', STR_PAD_LEFT); // 00001

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

        return redirect()->route('admin.batches.index')->with('success', 'Batch created with auto barcode.');
    }


    public function print(Request $request, \App\Models\Batch $batch)
{
    $w = (float) $request->query('w', 2.0); // inches
    $h = (float) $request->query('h', 1.0);

    // basic safety limits
    if ($w <= 0 || $w > 10) $w = 2.0;
    if ($h <= 0 || $h > 10) $h = 1.0;

    $batch->load('perfume', 'shop');

    return view('shared.batches.print', compact('batch', 'w', 'h'));
}

}
