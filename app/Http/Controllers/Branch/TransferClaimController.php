<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BatchTransfer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            $code = trim($data['code']);

            $transfer = BatchTransfer::query()
                ->where('code', $code)
                ->lockForUpdate()
                ->first();

            abort_if(!$transfer, 404, 'Invalid code.');
            abort_if($transfer->status !== 'pending', 422, 'This code is already used or cancelled.');
            abort_if((int)$transfer->to_shop_id !== (int)$branch->id, 403, 'This code is not for your branch.');

            // Load batches + perfume for item_name
            $transfer->load(['items.batch.perfume']);

            abort_if($transfer->items->count() === 0, 422, 'Transfer has no items.');

            $mainShopId = (int)$transfer->from_shop_id;

            // ---------------------------
            // 1) Create INTERNAL SALE (Main -> Branch) with 15% discount
            // ---------------------------
            $sale = Sale::create([
                'shop_id'         => $mainShopId,
                'user_id'         => auth()->id(),

                // Store branch as "customer"
                'customer_name'   => $branch->name ?? ('Branch #'.$branch->id),
                'customer_phone'  => $branch->phone ?? null, // if you have phone column on shops, else null ok

                'subtotal'        => 0,
                'discount_type'   => 'percent',
                'discount_value'  => 15,
                'discount_amount' => 0,
                'grand_total'     => 0,

                'status'          => 'completed', // or 'completed'/'paid' whatever you use
            ]);

            $subtotal = 0.0;

            foreach ($transfer->items as $item) {
                $mainBatch = $item->batch;
                abort_if(!$mainBatch, 422, 'Batch missing for one of the transfer items.');

                // Safety check: item batch must belong to main shop
                abort_if((int)$mainBatch->shop_id !== $mainShopId, 422, 'Invalid transfer batch source.');

                $qty = (int)$item->quantity;
                abort_if($qty <= 0, 422, 'Invalid transfer quantity.');

                $sell = (float)($mainBatch->sell_price ?? 0);
                abort_if($sell <= 0, 422, 'Selling price missing for batch: '.$mainBatch->barcode);

                $line = round($sell * $qty, 2);
                $subtotal += $line;

                // Sale item in MAIN shop sale
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'batch_id'   => $mainBatch->id,
                    'barcode'    => $mainBatch->barcode,
                    'item_name'  => $mainBatch->perfume?->name ?? 'Perfume',
                    'unit_price' => round($sell, 2),
                    'quantity'   => $qty,
                    'line_total' => $line,
                ]);

                // ---------------------------
                // 2) Upsert BRANCH batch with same barcode
                //    - sell_price = main sell_price
                //    - cost_price = sell_price * 0.85
                // ---------------------------
                $branchSell = round($sell, 2);
                $branchCost = round($branchSell * 0.85, 2);

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
                        'cost_price' => $branchCost,
                        'sell_price' => $branchSell,
                        'mfg_date'   => $mainBatch->mfg_date,
                        'exp_date'   => $mainBatch->exp_date,
                        'is_active'  => true,
                    ]);
                } else {
                    // enforce pricing rules on every claim
                    $branchBatch->sell_price = $branchSell;
                    $branchBatch->cost_price = $branchCost;
                    $branchBatch->save();
                }

                $branchBatch->increment('quantity', $qty);

                // ✅ Keep your existing rule:
                // DO NOT deduct from main shop here if already deducted when transfer created.
            }

            $subtotal = round($subtotal, 2);
            $discountAmount = round($subtotal * 0.15, 2);
            $grandTotal = round($subtotal - $discountAmount, 2);

            $sale->subtotal = $subtotal;
            $sale->discount_amount = $discountAmount;
            $sale->grand_total = $grandTotal;
            $sale->save();

            // Payment record (COUNTER) as requested
            Payment::create([
                'shop_id' => $mainShopId,
                'sale_id' => $sale->id,
                'method'  => 'counter',
                'bank_id' => null,
                'amount'  => $grandTotal,
                'paid_at' => now(),
            ]);

            // ---------------------------
            // 3) Mark transfer claimed
            // ---------------------------
            $transfer->status     = 'claimed';
            $transfer->claimed_at = now();

            if (Schema::hasColumn('batch_transfers', 'claimed_by')) {
                $transfer->claimed_by = auth()->id();
            }

            // Optional: save sale_id if column exists (no migration required)
            if (Schema::hasColumn('batch_transfers', 'sale_id')) {
                $transfer->sale_id = $sale->id;
            }

            $transfer->save();

            DB::commit();

            return back()->with('success', 'Transfer claimed. Code: '.$transfer->code.' | Main sale: #'.$sale->id);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

}
