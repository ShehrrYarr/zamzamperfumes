<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BatchTransfer;
use App\Models\BatchTransferItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchTransferController extends Controller
{
    private function branchShopOrFail(): Shop
    {
        $user = auth()->user();
        $shop = Shop::find($user->shop_id);
        abort_if(!$shop || $shop->type !== 'branch', 403);
        return $shop;
    }

    public function index(Request $request)
    {
        $branch = $this->branchShopOrFail();

        $status = $request->get('status', 'all');
        $from = $request->get('from');
        $to = $request->get('to');
        $direction = $request->get('direction', 'all'); // all|sent|received

        $q = BatchTransfer::query()
            ->with([
                'fromShop',
                'toShop',
                'items.batch.perfume',
            ])
            ->orderByDesc('id');

        // Restrict: branch can only see its own sent/received transfers
        $q->where(function ($w) use ($branch) {
            $w->where('from_shop_id', $branch->id)
              ->orWhere('to_shop_id', $branch->id);
        });

        if ($direction === 'sent') {
            $q->where('from_shop_id', $branch->id);
        } elseif ($direction === 'received') {
            $q->where('to_shop_id', $branch->id);
        }

        if ($status && $status !== 'all') {
            $q->where('status', $status);
        }
        if ($from) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $q->whereDate('created_at', '<=', $to);
        }

        $transfers = $q->paginate(25)->withQueryString();

        return view('panels.branch.transfers.index', compact('branch', 'transfers', 'status', 'from', 'to', 'direction'));
    }

    public function create()
    {
        $branch = $this->branchShopOrFail();

        // To branches only (not main), not self
        $branches = Shop::query()
            ->where('type', 'branch')
            ->where('id', '!=', $branch->id)
            ->orderBy('name')
            ->get();

        $batches = Batch::query()
            ->with('perfume')
            ->where('shop_id', $branch->id)
            ->where('quantity', '>', 0)
            ->orderByDesc('id')
            ->get();

        return view('panels.branch.transfers.create', compact('branch', 'branches', 'batches'));
    }

    public function store(Request $request)
    {
        $branch = $this->branchShopOrFail();

        $data = $request->validate([
            'to_shop_id' => ['required', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batch_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Ensure target is a BRANCH and not same as sender
        $toShop = Shop::query()
            ->where('id', (int)$data['to_shop_id'])
            ->where('type', 'branch')
            ->first();

        abort_if(!$toShop, 422, 'Invalid destination branch.');
        abort_if((int)$toShop->id === (int)$branch->id, 422, 'Cannot transfer to the same branch.');

        return DB::transaction(function () use ($branch, $toShop, $data) {

            // Validate batches belong to current branch + have enough qty (no deduction here, but validate now)
            $items = collect($data['items'])
                ->map(function ($row) {
                    return [
                        'batch_id' => (int)$row['batch_id'],
                        'quantity' => (int)$row['quantity'],
                    ];
                })
                ->filter(fn($r) => $r['batch_id'] > 0 && $r['quantity'] > 0)
                ->values();

            abort_if($items->count() === 0, 422, 'Please select at least one batch.');

            $batchIds = $items->pluck('batch_id')->unique()->values();

            $batches = Batch::query()
                ->with('perfume')
                ->where('shop_id', $branch->id)
                ->whereIn('id', $batchIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            abort_if($batches->count() !== $batchIds->count(), 422, 'One or more batches are invalid.');

            foreach ($items as $row) {
                $b = $batches->get($row['batch_id']);
                abort_if(!$b, 422, 'Invalid batch.');
                abort_if((int)$b->quantity < (int)$row['quantity'], 422, "Insufficient stock for barcode {$b->barcode}.");
            }

            // Create transfer
            $code = strtoupper(Str::random(10));

            // Ensure unique code
            while (BatchTransfer::where('code', $code)->exists()) {
                $code = strtoupper(Str::random(10));
            }

            $transfer = BatchTransfer::create([
                'code' => $code,
                'from_shop_id' => $branch->id,
                'to_shop_id' => $toShop->id,
                'status' => 'pending',
            ]);

            foreach ($items as $row) {
                BatchTransferItem::create([
                    'batch_transfer_id' => $transfer->id,
                    'batch_id' => $row['batch_id'],
                    'quantity' => $row['quantity'],
                ]);
            }

            return redirect()
                ->route('branch.transfers.index')
                ->with('success', "Transfer code created: {$transfer->code}");
        });
    }
}