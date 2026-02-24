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



private function assertAdmin(): void
{
    abort_if(!auth()->check(), 403);
    abort_if(auth()->user()->role !== 'admin', 403);
}


   public function index(Request $request)
{
    $mainShop = Shop::where('type', 'main')->first();
    abort_if(!$mainShop, 404, 'Main shop not found.');

    $q = trim((string)$request->query('q', ''));

    $batchesQ = Batch::query()
        ->with('perfume')
        ->where('shop_id', $mainShop->id)
        ->when($q !== '', function ($qq) use ($q) {
            // barcode search (contains)
            $qq->where('barcode', 'like', "%{$q}%");
        })
        ->orderByDesc('id');

    // ✅ If ajax request, return JSON so table can update live
    if ($request->wantsJson()) {
        $rows = $batchesQ->limit(200)->get()->map(function ($b) {
            return [
                'id' => $b->id,
                'barcode' => $b->barcode,
                'perfume' => $b->perfume?->name ?? '—',
                'quantity' => (int)$b->quantity,
                'sell_price' => $b->sell_price,
                'cost_price' => $b->cost_price,
                'print_url' => route('admin.batches.print', $b->id),
                // ✅ edit page (you will create this route/page)
                'edit_url' => route('admin.batches.edit_qty', $b->id),
                'edit_sell_price_url' => route('admin.batches.edit_sell_price', $b->id),
            ];
        });

        return response()->json([
            'ok' => true,
            'rows' => $rows,
        ]);
    }

    // Normal page load
    $batches = $batchesQ->get();

    return view('admin.batches.index', compact('batches', 'mainShop', 'q'));
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



public function editQty(Batch $batch)
{
    $this->assertAdmin();

    // Only allow editing MAIN shop batches (since this page is for admin main inventory)
    $mainShop = Shop::where('type', 'main')->first();
    abort_if(!$mainShop, 404, 'Main shop not found.');

    abort_if((int)$batch->shop_id !== (int)$mainShop->id, 403, 'You can only edit main shop batches.');

    $batch->load('perfume');

    return view('admin.batches.edit_qty', compact('batch', 'mainShop'));
}

public function updateQty(Request $request, Batch $batch)
{
    $this->assertAdmin();

    $mainShop = Shop::where('type', 'main')->first();
    abort_if(!$mainShop, 404, 'Main shop not found.');
    abort_if((int)$batch->shop_id !== (int)$mainShop->id, 403, 'You can only edit main shop batches.');

    $data = $request->validate([
        'quantity' => ['required', 'integer', 'min:0'],
        'note'     => ['nullable', 'string', 'max:255'], // optional
    ]);

    // Update quantity
    $batch->quantity = (int)$data['quantity'];
    $batch->save();

    // (Optional) If you later want audit logs, we’ll store this in a separate table.

    return redirect()
        ->route('admin.batches.index')
        ->with('success', "Quantity updated for barcode {$batch->barcode}.");
}


public function editSellPrice(Batch $batch)
{
    abort_if(!auth()->check(), 403);
    abort_if(auth()->user()->role !== 'admin', 403);

    // We edit "globally" based on barcode
    $barcode = $batch->barcode;

    // Show how many batches will be impacted + where they exist
    $targets = Batch::with('shop')
        ->where('barcode', $barcode)
        ->orderBy('shop_id')
        ->get();

    return view('admin.batches.edit_sell_price', compact('batch', 'targets', 'barcode'));
}

public function updateSellPrice(Request $request, Batch $batch)
{
    abort_if(!auth()->check(), 403);
    abort_if(auth()->user()->role !== 'admin', 403);

    $data = $request->validate([
        'sell_price' => ['required', 'numeric', 'min:0'],
    ]);

    $barcode = $batch->barcode;
    $newPrice = (float)$data['sell_price'];

    DB::transaction(function () use ($barcode, $newPrice) {
        // ✅ Update ALL batches across main + branches that share this barcode
        Batch::where('barcode', $barcode)->update([
            'sell_price' => $newPrice,
            'updated_at' => now(),
        ]);
    });

    return redirect()
        ->route('admin.batches.index', ['q' => $barcode])
        ->with('success', "Sell price updated for barcode {$barcode} in all shops/branches.");
}

}
