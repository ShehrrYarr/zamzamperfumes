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

    

//     public function claim(Request $request)
// {
//     $branch = $this->branchShopOrFail();

//     $data = $request->validate([
//         'code' => ['required', 'string', 'max:50'],
//     ]);

//     try {
//         DB::beginTransaction();

//         $transfer = \App\Models\BatchTransfer::query()
//             ->where('code', trim($data['code']))
//             ->lockForUpdate()
//             ->first();

//         abort_if(!$transfer, 404, 'Invalid code.');
//         abort_if($transfer->status !== 'pending', 422, 'This code is already used or cancelled.');
//         abort_if((int)$transfer->to_shop_id !== (int)$branch->id, 403, 'This code is not for your branch.');

//         $transfer->load(['items.batch.perfume']); // batch contains barcode/perfume info

//         abort_if($transfer->items->count() === 0, 422, 'Transfer has no items.');

//         // We need main shop id for the internal sale
//         $mainShopId = (int)$transfer->from_shop_id;

//         // -----------------------------
//         // 1) Move stock to branch (NO main deduct here)
//         // 2) Build internal sale subtotal from sell_price
//         // -----------------------------
//         $subtotal = 0.0;

//         // We'll also lock/create branch batches carefully
//         foreach ($transfer->items as $item) {
//             $mainBatch = $item->batch;
//             abort_if(!$mainBatch, 422, 'Batch missing for one of the transfer items.');

//             $qty = (int)$item->quantity;
//             abort_if($qty < 1, 422, 'Invalid quantity in transfer item.');

//             $sell = (float)($mainBatch->sell_price ?? 0);
//             $line = $sell * $qty;
//             $subtotal += $line;

//             // Branch batch by barcode (unique per shop)
//             $branchBatch = \App\Models\Batch::query()
//                 ->where('shop_id', $branch->id)
//                 ->where('barcode', $mainBatch->barcode)
//                 ->lockForUpdate()
//                 ->first();

//             $branchCost = round($sell * 0.85, 2); // 15% discount

//             if (!$branchBatch) {
//                 $branchBatch = \App\Models\Batch::create([
//                     'perfume_id' => $mainBatch->perfume_id,
//                     'shop_id'    => $branch->id,
//                     'barcode'    => $mainBatch->barcode,
//                     'batch_no'   => $mainBatch->batch_no,
//                     'quantity'   => 0,
//                     'cost_price' => $branchCost,       // ✅ branch cost = 15% less than sell
//                     'sell_price' => $sell,             // ✅ keep same sell price
//                     'mfg_date'   => $mainBatch->mfg_date,
//                     'exp_date'   => $mainBatch->exp_date,
//                     'is_active'  => true,
//                 ]);
//             } else {
//                 // Keep selling price same; ensure cost price follows rule for transferred stock
//                 $branchBatch->sell_price = $sell;
//                 $branchBatch->cost_price = $branchCost;
//                 $branchBatch->save();
//             }

//             $branchBatch->increment('quantity', $qty);
//         }

//         // -----------------------------
//         // 3) Create INTERNAL sale in MAIN shop
//         //    discount = 15% (percent)
//         //    payment = counter
//         // -----------------------------
//         $discountType = 'percent';
//         $discountValue = 15.0;
//         $discountAmount = round(min(($subtotal * 0.15), $subtotal), 2);
//         $grandTotal = round(max($subtotal - $discountAmount, 0), 2);

//         $sale = \App\Models\Sale::create([
//             'shop_id'         => $mainShopId,
//             'user_id'         => auth()->id(),
//             'customer_name'   => 'Branch Transfer — ' . ($branch->name ?? ('Branch#'.$branch->id)),
//             'customer_phone'  => null,

//             'subtotal'        => round($subtotal, 2),
//             'discount_type'   => $discountType,
//             'discount_value'  => round($discountValue, 2),
//             'discount_amount' => $discountAmount,
//             'grand_total'     => $grandTotal,

//             'status'          => 'completed',

//             // ✅ flags for internal return logic
//             'sale_type'       => 'internal_transfer',
//             'related_shop_id' => (int)$branch->id,
//             'transfer_id'     => (int)$transfer->id,
//         ]);

//         // Sale items reference MAIN batches (important for later returns)
//         foreach ($transfer->items as $item) {
//             $mainBatch = $item->batch;
//             $qty = (int)$item->quantity;

//             $sell = (float)($mainBatch->sell_price ?? 0);
//             $line = $sell * $qty;

//             \App\Models\SaleItem::create([
//                 'sale_id'    => $sale->id,
//                 'batch_id'   => $mainBatch->id,                  // ✅ main batch id
//                 'barcode'    => $mainBatch->barcode,
//                 'item_name'  => $mainBatch->perfume?->name ?? ('Batch#'.$mainBatch->id),
//                 'unit_price' => round($sell, 2),
//                 'quantity'   => $qty,
//                 'line_total' => round($line, 2),
//             ]);
//         }

//         // Payment record as counter
//         \App\Models\Payment::create([
//             'shop_id' => $mainShopId,
//             'sale_id' => $sale->id,
//             'method'  => 'counter',
//             'bank_id' => null,
//             'amount'  => $grandTotal,
//             'paid_at' => now(),
//         ]);

//         // Mark transfer claimed
//         $transfer->status     = 'claimed';
//         $transfer->claimed_at = now();

//         if (\Schema::hasColumn('batch_transfers', 'claimed_by')) {
//             $transfer->claimed_by = auth()->id();
//         }

//         $transfer->save();

//         DB::commit();

//         return back()->with('success', 'Transfer claimed + internal sale created. Code: '.$transfer->code.' | Sale #'.$sale->id);
//     } catch (\Throwable $e) {
//         DB::rollBack();
//         return back()->with('error', $e->getMessage());
//     }
// }

public function claim(Request $request)
{
    $branch = $this->branchShopOrFail();

    $data = $request->validate([
        'code' => ['required', 'string', 'max:50'],
    ]);

    try {
        DB::beginTransaction();

        $transfer = \App\Models\BatchTransfer::query()
            ->where('code', trim($data['code']))
            ->lockForUpdate()
            ->first();

        abort_if(!$transfer, 404, 'Invalid code.');
        abort_if($transfer->status !== 'pending', 422, 'This code is already used or cancelled.');
        abort_if((int)$transfer->to_shop_id !== (int)$branch->id, 403, 'This code is not for your branch.');

        $transfer->load(['items.batch.perfume']);
        abort_if($transfer->items->count() === 0, 422, 'Transfer has no items.');

        $mainShopId = (int)$transfer->from_shop_id;

        // -----------------------------
        // 1) Move stock to branch + compute subtotal
        // -----------------------------
        $subtotal = 0.0;

        foreach ($transfer->items as $item) {
            $mainBatch = $item->batch;
            abort_if(!$mainBatch, 422, 'Batch missing for one of the transfer items.');

            $qty = (int)$item->quantity;
            abort_if($qty < 1, 422, 'Invalid quantity in transfer item.');

            $sell = (float)($mainBatch->sell_price ?? 0);
            $subtotal += ($sell * $qty);

            $branchBatch = \App\Models\Batch::query()
                ->where('shop_id', $branch->id)
                ->where('barcode', $mainBatch->barcode)
                ->lockForUpdate()
                ->first();

            $branchCost = round($sell * 0.85, 2); // 15% less than sell

            if (!$branchBatch) {
                $branchBatch = \App\Models\Batch::create([
                    'perfume_id' => $mainBatch->perfume_id,
                    'shop_id'    => $branch->id,
                    'barcode'    => $mainBatch->barcode,
                    'batch_no'   => $mainBatch->batch_no,
                    'quantity'   => 0,
                    'cost_price' => $branchCost,
                    'sell_price' => $sell,
                    'mfg_date'   => $mainBatch->mfg_date,
                    'exp_date'   => $mainBatch->exp_date,
                    'is_active'  => true,
                ]);
            } else {
                // keep sell same; cost should follow rule for transferred stock
                $branchBatch->sell_price = $sell;
                $branchBatch->cost_price = $branchCost;
                $branchBatch->save();
            }

            $branchBatch->increment('quantity', $qty);
        }

        // -----------------------------
        // 2) Create INTERNAL sale in MAIN shop (15% discount)
        // -----------------------------
        $discountAmount = round(min(($subtotal * 0.15), $subtotal), 2);
        $grandTotal = round(max($subtotal - $discountAmount, 0), 2);

        $sale = \App\Models\Sale::create([
            'shop_id'         => $mainShopId,
            'user_id'         => auth()->id(),
            'customer_name'   => 'Branch Transfer — ' . ($branch->name ?? ('Branch#'.$branch->id)),
            'customer_phone'  => null,

            'subtotal'        => round($subtotal, 2),
            'discount_type'   => 'percent',
            'discount_value'  => 15.0,
            'discount_amount' => $discountAmount,
            'grand_total'     => $grandTotal,

            'status'          => 'completed',

            // flags for your internal logic
            'sale_type'       => 'internal_transfer',
            'related_shop_id' => (int)$branch->id,
            'transfer_id'     => (int)$transfer->id,
        ]);

        foreach ($transfer->items as $item) {
            $mainBatch = $item->batch;
            $qty = (int)$item->quantity;

            $sell = (float)($mainBatch->sell_price ?? 0);

            \App\Models\SaleItem::create([
                'sale_id'    => $sale->id,
                'batch_id'   => $mainBatch->id, // main batch id
                'barcode'    => $mainBatch->barcode,
                'item_name'  => $mainBatch->perfume?->name ?? ('Batch#'.$mainBatch->id),
                'unit_price' => round($sell, 2),
                'quantity'   => $qty,
                'line_total' => round($sell * $qty, 2),
            ]);
        }

        \App\Models\Payment::create([
            'shop_id' => $mainShopId,
            'sale_id' => $sale->id,
            'method'  => 'counter',
            'bank_id' => null,
            'amount'  => $grandTotal,
            'paid_at' => now(),
        ]);

        // -----------------------------
        // 3) Accounts posting (YOUR schema: debit/credit)
        // -----------------------------
        $accountsWarning = null;

        try {
            $branchAccount = \App\Models\Account::query()
                ->where('shop_id', (int)$branch->id)
                ->orderByDesc('id')
                ->first();

            $mainAccount = \App\Models\Account::query()
                ->where('shop_id', (int)$mainShopId)
                ->orderByDesc('id')
                ->first();

            if (!$branchAccount || !$mainAccount) {
                $accountsWarning = 'Account entry skipped: account not found for branch/main.';
            } else {
                $refType = 'batch_transfer_claim';
                $refId   = (int)$transfer->id;

                $already = \App\Models\AccountEntry::query()
                    ->where('ref_type', $refType)
                    ->where('ref_id', $refId)
                    ->exists();

                if (!$already) {
                    $desc = "Transfer {$transfer->code} claimed | Internal Sale #{$sale->id} | 15% discount";

                    // Branch: DEBIT (branch owes main)
                    \App\Models\AccountEntry::create([
                        'account_id'  => $branchAccount->id,
                        'shop_id'     => (int)$branch->id,
                        'user_id'     => auth()->id(),
                        'entry_date'  => now()->toDateString(),
                        'debit'       => $grandTotal,
                        'credit'      => 0,
                        'description' => $desc,
                        'ref_type'    => $refType,
                        'ref_id'      => $refId,
                    ]);

                    // Main: CREDIT (main will receive)
                    \App\Models\AccountEntry::create([
                        'account_id'  => $mainAccount->id,
                        'shop_id'     => (int)$mainShopId,
                        'user_id'     => auth()->id(),
                        'entry_date'  => now()->toDateString(),
                        'debit'       => 0,
                        'credit'      => $grandTotal,
                        'description' => $desc,
                        'ref_type'    => $refType,
                        'ref_id'      => $refId,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Do not break claim
            $accountsWarning = 'Account entry failed: ' . $e->getMessage();
        }

        // -----------------------------
        // 4) Mark transfer claimed
        // -----------------------------
        $transfer->status     = 'claimed';
        $transfer->claimed_at = now();

        if (\Schema::hasColumn('batch_transfers', 'claimed_by')) {
            $transfer->claimed_by = auth()->id();
        }

        $transfer->save();

        DB::commit();

        $msg = 'Transfer claimed + internal sale created. Code: '.$transfer->code.' | Sale #'.$sale->id;

        if ($accountsWarning) {
            return back()->with('success', $msg)->with('warning', $accountsWarning);
        }

        return back()->with('success', $msg.' | Accounts posted ✅');

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

}
