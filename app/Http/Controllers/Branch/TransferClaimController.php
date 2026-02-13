<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BatchTransfer;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferClaimController extends Controller
{
    private function branchShopOrFail(): Shop
    {
        abort_if(!auth()->check(), 403);

        $user = auth()->user();
        $shop = Shop::find($user->shop_id);

        abort_if(!$shop || $shop->type !== 'branch', 403);
        return $shop;
    }

    public function showClaimForm()
    {
        $this->branchShopOrFail();
        return view('panels.branch.transfers.claim');
    }

    public function claim(Request $request)
    {
        $branch = $this->branchShopOrFail();

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        try {
            DB::beginTransaction();

            $transfer = BatchTransfer::query()
                ->where('code', trim($data['code']))
                ->lockForUpdate()
                ->first();

            abort_if(!$transfer, 404, 'Invalid code.');
            abort_if($transfer->status !== 'pending', 422, 'This code is already used or cancelled.');
            abort_if((int)$transfer->to_shop_id !== (int)$branch->id, 403, 'This code is not for your branch.');

            $transfer->load('items.batch'); // batch contains barcode/perfume info

            abort_if($transfer->items->count() === 0, 422, 'Transfer has no items.');

            foreach ($transfer->items as $item) {
                $mainBatch = $item->batch; // this is the ORIGINAL batch row (shop_id = main shop)
                abort_if(!$mainBatch, 422, 'Batch missing for one of the transfer items.');

                // ✅ DO NOT deduct from main shop here (already deducted at transfer creation)

                // 1) find or create branch batch with SAME barcode
                $branchBatch = Batch::query()
                    ->where('shop_id', $branch->id)
                    ->where('barcode', $mainBatch->barcode)
                    ->lockForUpdate()
                    ->first();

                if (!$branchBatch) {
                    $branchBatch = Batch::create([
                        'perfume_id' => $mainBatch->perfume_id,
                        'shop_id'    => $branch->id,
                        'barcode'    => $mainBatch->barcode,
                        'batch_no'   => $mainBatch->batch_no,
                        'quantity'   => 0,
                        'cost_price' => $mainBatch->cost_price,
                        'sell_price' => $mainBatch->sell_price,
                        'mfg_date'   => $mainBatch->mfg_date,
                        'exp_date'   => $mainBatch->exp_date,
                        'is_active'  => true,
                    ]);
                }

                // 2) add quantity
                $branchBatch->increment('quantity', (int)$item->quantity);
            }

            $transfer->status     = 'claimed';
            $transfer->claimed_at = now();

            // optional if you have claimed_by column
            if (\Schema::hasColumn('batch_transfers', 'claimed_by')) {
                $transfer->claimed_by = auth()->id();
            }

            $transfer->save();

            DB::commit();

            return back()->with('success', 'Transfer claimed successfully. Code: ' . $transfer->code);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
