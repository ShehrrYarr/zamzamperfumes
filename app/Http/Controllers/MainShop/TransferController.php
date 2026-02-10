<?php

namespace App\Http\Controllers\MainShop;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BatchTransfer;
use App\Models\BatchTransferItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferController extends Controller
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

    $status = $request->query('status', 'all');
    $branchId = $request->query('branch_id');
    $from = $request->query('from');
    $to = $request->query('to');

    $branches = \App\Models\Shop::where('type', 'branch')->orderBy('name')->get();

    $query = \App\Models\BatchTransfer::with(['toShop', 'items.batch.perfume'])
        ->where('from_shop_id', $mainShop->id);

    if ($status !== 'all') {
        $query->where('status', $status);
    }

    if ($branchId) {
        $query->where('to_shop_id', $branchId);
    }

    if ($from) {
        $query->whereDate('created_at', '>=', $from);
    }

    if ($to) {
        $query->whereDate('created_at', '<=', $to);
    }

    $transfers = $query->orderByDesc('id')->get();

    return view('panels.main.transfers.index', compact(
        'transfers', 'branches', 'status', 'branchId', 'from', 'to'
    ));
}


    public function create()
    {
        $mainShop = $this->mainShopOrFail();

        $branches = Shop::where('type', 'branch')->where('is_active', true)->orderBy('name')->get();

        $batches = Batch::with('perfume')
            ->where('shop_id', $mainShop->id)
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderByDesc('id')
            ->get();

        return view('panels.main.transfers.create', compact('branches', 'batches'));
    }

    public function store(Request $request)
    {
        $mainShop = $this->mainShopOrFail();

        $data = $request->validate([
            'to_shop_id' => ['required','exists:shops,id'],
            'batch_id'   => ['required','exists:batches,id'],
            'quantity'   => ['required','integer','min:1'],
        ]);

        $toShop = Shop::findOrFail($data['to_shop_id']);
        abort_if($toShop->type !== 'branch', 422, 'Target must be a branch.');

        $transfer = DB::transaction(function () use ($data, $mainShop, $toShop) {

            // lock the main batch row to safely check quantity
            $batch = Batch::where('id', $data['batch_id'])
                ->where('shop_id', $mainShop->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($batch->quantity < (int)$data['quantity'], 422, 'Not enough quantity in main shop.');

            // generate unique code
            do {
                $code = strtoupper(Str::random(10)); // like: A9K2P0X1QZ
            } while (BatchTransfer::where('code', $code)->exists());

            $transfer = BatchTransfer::create([
                'from_shop_id' => $mainShop->id,
                'to_shop_id'   => $toShop->id,
                'code'         => $code,
                'status'       => 'pending',
            ]);

            BatchTransferItem::create([
                'batch_transfer_id' => $transfer->id,
                'batch_id'          => $batch->id,
                'quantity'          => (int)$data['quantity'],
            ]);

            return $transfer->load(['toShop','items.batch.perfume']);
        });

        return redirect()
            ->route('main.transfers.index')
            ->with('success', 'Transfer created. Secret Code: '.$transfer->code);
    }

    public function cancel(\App\Models\BatchTransfer $transfer)
{
    $mainShop = $this->mainShopOrFail();

    abort_if((int)$transfer->from_shop_id !== (int)$mainShop->id, 403);
    abort_if($transfer->status !== 'pending', 422, 'Only pending transfers can be cancelled.');

    $transfer->status = 'cancelled';
    $transfer->save();

    return back()->with('success', 'Transfer cancelled: '.$transfer->code);
}

}
