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
            'code' => ['required','string','max:50'],
        ]);

        $result = DB::transaction(function () use ($data, $branch) {

            $transfer = BatchTransfer::where('code', $data['code'])
                ->lockForUpdate()
                ->first();

            abort_if(!$transfer, 404, 'Invalid code.');
            abort_if($transfer->status !== 'pending', 422, 'This code is already used or cancelled.');
            abort_if((int)$transfer->to_shop_id !== (int)$branch->id, 403, 'This code is not for your branch.');

            $transfer->load('items.batch');

            foreach ($transfer->items as $item) {
                $mainBatch = Batch::where('id', $item->batch_id)
                    ->where('shop_id', $transfer->from_shop_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_if($mainBatch->quantity < $item->quantity, 422, 'Main shop stock is insufficient now.');

                // 1) decrease main quantity
                $mainBatch->quantity -= $item->quantity;
                $mainBatch->save();

                // 2) create/update branch batch with SAME barcode (unique per shop)
                $branchBatch = Batch::where('shop_id', $branch->id)
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

                $branchBatch->quantity += $item->quantity;
                $branchBatch->save();
            }

            $transfer->status = 'claimed';
            $transfer->claimed_at = now();
            $transfer->save();

            return $transfer;
        });

        return back()->with('success', 'Transfer claimed successfully. Code: '.$result->code);
    }
}
