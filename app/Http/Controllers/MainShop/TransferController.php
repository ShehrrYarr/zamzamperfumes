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
        abort_if(!auth()->check(), 403);

        $user = auth()->user();
        $shop = Shop::find($user->shop_id);

        abort_if(!$shop || $shop->type !== 'main', 403);
        return $shop;
    }

    public function index(Request $request)
    {
        $mainShop = $this->mainShopOrFail();

        $status   = $request->query('status', 'all');
        $branchId = $request->query('branch_id');
        $from     = $request->query('from');
        $to       = $request->query('to');

        $branches = Shop::where('type', 'branch')->orderBy('name')->get();

        $query = BatchTransfer::with(['toShop', 'items.batch.perfume'])
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

        $branches = Shop::where('type', 'branch')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

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
            'to_shop_id' => ['required', 'exists:shops,id'],

            // multi-items
            'items' => ['required', 'array', 'min:1'],
            'items.*.batch_id' => ['required', 'integer', 'exists:batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $toShop = Shop::findOrFail((int)$data['to_shop_id']);
        abort_if($toShop->type !== 'branch', 422, 'Target must be a branch.');

        // Merge duplicate batch_ids (if user selected same batch twice)
        $merged = [];
        foreach ($data['items'] as $row) {
            $bid = (int)($row['batch_id'] ?? 0);
            $qty = (int)($row['quantity'] ?? 0);
            if ($bid <= 0 || $qty <= 0) continue;
            $merged[$bid] = ($merged[$bid] ?? 0) + $qty;
        }
        abort_if(count($merged) === 0, 422, 'Please add at least one batch item.');

        $transfer = DB::transaction(function () use ($merged, $mainShop, $toShop) {

            $batchIds = array_keys($merged);

            // lock all involved batches for safe deduction
            $batches = Batch::query()
                ->where('shop_id', $mainShop->id)
                ->whereIn('id', $batchIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate quantities
            foreach ($merged as $bid => $qty) {
                $batch = $batches->get($bid);
                abort_if(!$batch, 422, 'Invalid batch selected.');
                abort_if((int)$batch->quantity < $qty, 422, 'Not enough quantity for barcode '.$batch->barcode);
            }

            // Generate unique code
            do {
                $code = strtoupper(Str::random(10));
            } while (BatchTransfer::where('code', $code)->exists());

            $transfer = BatchTransfer::create([
                'from_shop_id' => $mainShop->id,
                'to_shop_id'   => $toShop->id,
                'code'         => $code,
                'status'       => 'pending',
            ]);

            // Create items + deduct quantities
            foreach ($merged as $bid => $qty) {
                $batch = $batches[$bid];

                BatchTransferItem::create([
                    'batch_transfer_id' => $transfer->id,
                    'batch_id'          => $batch->id,
                    'quantity'          => (int)$qty,
                ]);

                $batch->decrement('quantity', (int)$qty);
            }

            return $transfer->load(['toShop', 'items.batch.perfume']);
        });

        return redirect()
            ->route('main.transfers.index')
            ->with('success', 'Transfer created. Secret Code: ' . $transfer->code);
    }

   public function cancel(BatchTransfer $transfer)
{
    $mainShop = $this->mainShopOrFail();

    abort_if((int)$transfer->from_shop_id !== (int)$mainShop->id, 403);
    abort_if($transfer->status !== 'pending', 422, 'Only pending transfers can be cancelled.');

    DB::transaction(function () use ($transfer, $mainShop) {

        // Reload & lock the transfer row to avoid double-cancel race
        $t = BatchTransfer::where('id', $transfer->id)
            ->lockForUpdate()
            ->firstOrFail();

        abort_if($t->status !== 'pending', 422, 'Only pending transfers can be cancelled.');

        // Load items (what was deducted)
        $items = BatchTransferItem::where('batch_transfer_id', $t->id)->get();

        if ($items->isNotEmpty()) {
            $batchIds = $items->pluck('batch_id')->unique()->values()->all();

            // Lock all involved batches in main shop
            $batches = Batch::query()
                ->where('shop_id', $mainShop->id)
                ->whereIn('id', $batchIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Restore quantities
            foreach ($items as $it) {
                $bid = (int)$it->batch_id;
                $qty = (int)$it->quantity;

                $batch = $batches->get($bid);
                abort_if(!$batch, 422, "Batch not found in main inventory for restore (batch_id={$bid}).");

                $batch->increment('quantity', $qty);
            }
        }

        // Mark cancelled (after restore)
        $t->status = 'cancelled';
        $t->save();
    });

    return back()->with('success', 'Transfer cancelled (stock restored): ' . $transfer->code);
}
}
