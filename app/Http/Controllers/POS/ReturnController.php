<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReturnController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop, 403);
        abort_if(!in_array($shop->type, ['main','branch'], true), 403);
        return $shop;
    }


     private function internalRefundLineAmount(Sale $sale, float $lineSubtotal): float
    {
        $subtotal = (float)($sale->subtotal ?? 0);
        $grand    = (float)($sale->grand_total ?? 0);

        $dtype = $sale->discount_type ?? 'none';
        $dval  = (float)($sale->discount_value ?? 0);

        if ($lineSubtotal <= 0) return 0.0;

        if ($dtype === 'percent') {
            $pct = max(0, min($dval, 100));
            return round($lineSubtotal * (1 - ($pct / 100)), 2);
        }

        if ($dtype === 'flat') {
            if ($subtotal <= 0) return 0.0;
            $ratio = $grand / $subtotal;
            $ratio = max(0, min($ratio, 1));
            return round($lineSubtotal * $ratio, 2);
        }

        return round($lineSubtotal, 2);
    }

    private function postInternalReturnAccountingOrSkip(Sale $sale, SaleReturn $returnHeader, float $amount): void
    {
        if ($amount <= 0) return;

        $mainShopId   = (int)$sale->shop_id;
        $branchShopId = (int)($sale->related_shop_id ?? 0);
        if ($branchShopId <= 0) return;

        $refType = 'internal_transfer_return';
        $refId   = (int)$returnHeader->id;

        $already = AccountEntry::query()
            ->where('ref_type', $refType)
            ->where('ref_id', $refId)
            ->exists();

        if ($already) return;

        $branchAccount = Account::query()->where('shop_id', $branchShopId)->orderBy('id')->first();
        $mainAccount   = Account::query()->where('shop_id', $mainShopId)->orderBy('id')->first();
        if (!$branchAccount || !$mainAccount) return;

        $desc = "Internal Transfer Return | Sale #{$sale->id} | Return #{$returnHeader->id}";

        AccountEntry::create([
            'account_id'  => $branchAccount->id,
            'shop_id'     => $branchShopId,
            'user_id'     => auth()->id(),
            'entry_date'  => now()->toDateString(),
            'debit'       => 0,
            'credit'      => round($amount, 2),
            'description' => $desc,
            'ref_type'    => $refType,
            'ref_id'      => $refId,
        ]);

        AccountEntry::create([
            'account_id'  => $mainAccount->id,
            'shop_id'     => $mainShopId,
            'user_id'     => auth()->id(),
            'entry_date'  => now()->toDateString(),
            'debit'       => round($amount, 2),
            'credit'      => 0,
            'description' => $desc,
            'ref_type'    => $refType,
            'ref_id'      => $refId,
        ]);
    }


    // public function process(Request $request)
    // {
    //     $shop = $this->currentShopOrFail();
    //     $user = auth()->user();

    //     $data = $request->validate([
    //         'sale_id' => ['required','integer'],
    //         'method'  => ['nullable','in:counter,bank'], // optional
    //         'bank_id' => ['nullable','integer'],
    //     ]);

    //     try {
    //         $return = DB::transaction(function () use ($shop, $user, $data) {

    //             $sale = Sale::with(['items','payments'])
    //                 ->where('id', $data['sale_id'])
    //                 ->where('shop_id', $shop->id)
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             // Prevent full return if already fully returned
    //             if ($sale->status === 'returned') {
    //                 abort(422, 'This sale is already fully returned.');
    //             }

    //             // Refund method default = original payment method
    //             $origPay = $sale->payments->first();
    //             $method = $data['method'] ?? ($origPay?->method ?? 'counter');
    //             $bankId = $method === 'bank'
    //                 ? (int)($data['bank_id'] ?? $origPay?->bank_id)
    //                 : null;

    //             if ($method === 'bank' && empty($bankId)) {
    //                 abort(422, 'Please select a bank for bank refund.');
    //             }

    //             // Create return header
    //             $returnHeader = SaleReturn::create([
    //                 'shop_id' => $shop->id,
    //                 'sale_id' => $sale->id,
    //                 'user_id' => $user->id,
    //                 'refund_amount' => 0,
    //                 'method' => $method,
    //                 'bank_id' => $bankId,
    //             ]);

    //             $refundTotal = 0.0;

    //             foreach ($sale->items as $it) {
    //                 // Return FULL remaining qty for each item
    //                 $qty = (int)$it->quantity;

    //                 // Restore stock
    //                 $batch = Batch::where('id', $it->batch_id)
    //                     ->where('shop_id', $shop->id)
    //                     ->lockForUpdate()
    //                     ->first();

    //                 if ($batch) {
    //                     $batch->quantity = (int)$batch->quantity + $qty;
    //                     $batch->save();
    //                 }

    //                 $unit = (float)$it->unit_price;
    //                 $lineRefund = $unit * $qty;

    //                 SaleReturnItem::create([
    //                     'sale_return_id' => $returnHeader->id,
    //                     'sale_item_id'   => $it->id,
    //                     'batch_id'       => $it->batch_id,
    //                     'quantity'       => $qty,
    //                     'unit_price'     => round($unit, 2),
    //                     'line_refund'    => round($lineRefund, 2),
    //                 ]);

    //                 $refundTotal += $lineRefund;
    //             }

    //             $refundTotal = round($refundTotal, 2);

    //             $returnHeader->refund_amount = $refundTotal;
    //             $returnHeader->save();

    //             // Record refund payment negative
    //             Payment::create([
    //                 'shop_id' => $shop->id,
    //                 'sale_id' => $sale->id,
    //                 'method'  => $method,
    //                 'bank_id' => $bankId,
    //                 'amount'  => -1 * $refundTotal,
    //                 'paid_at' => now(),
    //             ]);

    //             // Mark sale as fully returned
    //             $sale->status = 'returned';
    //             $sale->save();

    //             return $returnHeader;
    //         });

    //         return response()->json([
    //             'ok' => true,
    //             'message' => 'Full return processed.',
    //             'return_id' => $return->id,
    //         ]);

    //     } catch (Throwable $e) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Return failed: '.$e->getMessage(),
    //         ], 500);
    //     }
    // }


     public function process(Request $request)
    {
        $shop = $this->currentShopOrFail();
        $user = auth()->user();

        $data = $request->validate([
            'sale_id' => ['required', 'integer'],
            'method'  => ['nullable', 'in:counter,bank'],
            'bank_id' => ['nullable', 'integer'],
        ]);

        try {
            $return = DB::transaction(function () use ($shop, $user, $data) {

                $sale = Sale::with(['items', 'payments'])
                    ->where('id', $data['sale_id'])
                    ->where('shop_id', $shop->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($sale->status === 'returned') {
                    abort(422, 'This sale is already fully returned.');
                }

                $isInternalTransfer = ($sale->sale_type ?? 'customer') === 'internal_transfer';
                $branchShopId = $isInternalTransfer ? (int)($sale->related_shop_id ?? 0) : 0;

                if ($isInternalTransfer) {
                    abort_if($shop->type !== 'main', 403, 'Only main shop can return internal transfer sales.');
                    abort_if($branchShopId <= 0, 422, 'Internal transfer sale missing branch link.');
                }

                // Refund method defaults to original
                $origPay = $sale->payments->first();
                $method = $data['method'] ?? ($origPay?->method ?? 'counter');
                $bankId = $method === 'bank'
                    ? (int)($data['bank_id'] ?? $origPay?->bank_id)
                    : null;

                if ($method === 'bank' && empty($bankId)) {
                    abort(422, 'Please select a bank for bank refund.');
                }

                $returnHeader = SaleReturn::create([
                    'shop_id' => $shop->id,
                    'sale_id' => $sale->id,
                    'user_id' => $user->id,
                    'refund_amount' => 0,
                    'method' => $method,
                    'bank_id' => $bankId,
                ]);

                $refundTotal = 0.0;

                // Return remaining quantities (current quantity)
                foreach ($sale->items as $it) {
                    $qty = (int)$it->quantity;
                    if ($qty <= 0) continue;

                    // STOCK
                    if ($isInternalTransfer) {
                        $branchBatch = Batch::where('shop_id', $branchShopId)
                            ->where('barcode', $it->barcode)
                            ->lockForUpdate()
                            ->first();

                        abort_if(!$branchBatch, 422, "Branch batch not found for barcode {$it->barcode}.");
                        abort_if((int)$branchBatch->quantity < $qty, 422, "Branch stock insufficient for barcode {$it->barcode}.");

                        $branchBatch->quantity = (int)$branchBatch->quantity - $qty;
                        $branchBatch->save();

                        $mainBatch = Batch::where('id', $it->batch_id)
                            ->where('shop_id', $shop->id)
                            ->lockForUpdate()
                            ->first();

                        if ($mainBatch) {
                            $mainBatch->quantity = (int)$mainBatch->quantity + $qty;
                            $mainBatch->save();
                        }
                    } else {
                        $batch = Batch::where('id', $it->batch_id)
                            ->where('shop_id', $shop->id)
                            ->lockForUpdate()
                            ->first();

                        if ($batch) {
                            $batch->quantity = (int)$batch->quantity + $qty;
                            $batch->save();
                        }
                    }

                    // REFUND
                    $unit = (float)$it->unit_price;
                    $lineSubtotal = $unit * $qty;

                    $lineRefund = $isInternalTransfer
                        ? $this->internalRefundLineAmount($sale, $lineSubtotal)
                        : round($lineSubtotal, 2);

                    SaleReturnItem::create([
                        'sale_return_id' => $returnHeader->id,
                        'sale_item_id'   => $it->id,
                        'batch_id'       => $it->batch_id,
                        'quantity'       => $qty,
                        'unit_price'     => round($unit, 2),
                        'line_refund'    => round($lineRefund, 2),
                    ]);

                    $refundTotal += $lineRefund;

                    // REMOVE item from sale completely
                    $it->delete();
                }

                $refundTotal = round($refundTotal, 2);

                $returnHeader->refund_amount = $refundTotal;
                $returnHeader->save();

                Payment::create([
                    'shop_id' => $shop->id,
                    'sale_id' => $sale->id,
                    'method'  => $method,
                    'bank_id' => $bankId,
                    'amount'  => -1 * $refundTotal,
                    'paid_at' => now(),
                ]);

                // Sale totals become zero (because items removed)
                $sale->subtotal = 0;
                $sale->discount_amount = 0;
                $sale->grand_total = 0;
                $sale->status = 'returned';
                $sale->save();

                // ✅ INTERNAL TRANSFER ACCOUNTS reverse posting
                if ($isInternalTransfer && $refundTotal > 0) {
                    $this->postInternalReturnAccountingOrSkip($sale, $returnHeader, $refundTotal);
                }

                return $returnHeader;
            });

            return response()->json([
                'ok' => true,
                'message' => 'Full return processed.',
                'return_id' => $return->id,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Return failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
