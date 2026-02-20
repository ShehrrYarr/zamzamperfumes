<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PartialReturnController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop, 403);
        abort_if(!in_array($shop->type, ['main','branch'], true), 403);
        return $shop;
    }

    public function sale(Sale $sale)
    {
        $shop = $this->currentShopOrFail();
        abort_if($sale->shop_id !== $shop->id, 403);

        $sale->load(['items', 'payments.bank']);

        $returnedMap = SaleReturnItem::selectRaw('sale_item_id, SUM(quantity) as returned_qty')
            ->whereIn('sale_item_id', $sale->items->pluck('id'))
            ->groupBy('sale_item_id')
            ->pluck('returned_qty','sale_item_id');

        return response()->json([
            'ok' => true,
            'sale' => [
                'id' => $sale->id,
                'customer' => $sale->customer_name ?: 'Walk-in',
                'phone' => $sale->customer_phone,
                'created_at' => optional($sale->created_at)->format('Y-m-d H:i'),
                'total' => (float)$sale->grand_total,
                'payment_method' => $sale->payments->first()?->method ?? 'counter',
                'bank_id' => $sale->payments->first()?->bank_id,
            ],
            'items' => $sale->items->map(function($it) use ($returnedMap){
                $returned = (int)($returnedMap[$it->id] ?? 0);
                $remaining = max(0, (int)$it->quantity - $returned);

                return [
                    'sale_item_id' => $it->id,
                    'batch_id' => $it->batch_id,
                    'name' => $it->item_name,
                    'barcode' => $it->barcode,
                    'sold_qty' => (int)$it->quantity,
                    'returned_qty' => $returned,
                    'returnable_qty' => $remaining,
                    'unit_price' => (float)$it->unit_price,
                ];
            }),
        ]);
    }

    public function process(Request $request)
    {
        $shop = $this->currentShopOrFail();
        $user = auth()->user();

        $data = $request->validate([
            'sale_id' => ['required','integer'],
            'method' => ['required','in:counter,bank'],
            'bank_id' => ['nullable','integer'],
            'items' => ['required','array','min:1'],
            'items.*.sale_item_id' => ['required','integer'],
            'items.*.qty' => ['required','integer','min:1'],
        ]);

        try {
            $return = DB::transaction(function () use ($shop, $user, $data) {

                $sale = Sale::with(['items','payments'])
                    ->where('id', $data['sale_id'])
                    ->where('shop_id', $shop->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // identify internal transfer sale
                $isInternalTransfer = ($sale->sale_type ?? 'customer') === 'internal_transfer';
                $branchShopId = $isInternalTransfer ? (int)($sale->related_shop_id ?? 0) : 0;

                if ($isInternalTransfer) {
                    abort_if($shop->type !== 'main', 403, 'Only main shop can return internal transfer sales.');
                    abort_if($branchShopId <= 0, 422, 'Internal transfer sale missing branch link.');
                }

                // Map returned qty already
                $returnedMap = SaleReturnItem::selectRaw('sale_item_id, SUM(quantity) as returned_qty')
                    ->whereIn('sale_item_id', $sale->items->pluck('id'))
                    ->groupBy('sale_item_id')
                    ->lockForUpdate()
                    ->pluck('returned_qty','sale_item_id');

                // Validate bank if method bank
                if ($data['method'] === 'bank' && empty($data['bank_id'])) {
                    abort(422, 'Please select a bank.');
                }

                $refundTotal = 0.0;

                $returnHeader = SaleReturn::create([
                    'shop_id' => $shop->id,
                    'sale_id' => $sale->id,
                    'user_id' => $user->id,
                    'refund_amount' => 0,
                    'method' => $data['method'],
                    'bank_id' => $data['method'] === 'bank' ? (int)$data['bank_id'] : null,
                ]);

                $saleItemsById = $sale->items->keyBy('id');

                foreach ($data['items'] as $row) {
                    $saleItemId = (int)$row['sale_item_id'];
                    $qty = (int)$row['qty'];

                    $saleItem = $saleItemsById->get($saleItemId);
                    if (!$saleItem) abort(422, 'Invalid sale item.');

                    $alreadyReturned = (int)($returnedMap[$saleItemId] ?? 0);
                    $returnable = max(0, (int)$saleItem->quantity - $alreadyReturned);

                    if ($qty > $returnable) {
                        abort(422, "Return qty exceeds allowed for {$saleItem->barcode}.");
                    }

                    // -----------------------------
                    // STOCK MOVEMENT
                    // -----------------------------
                    if ($isInternalTransfer) {
                        // 1) Decrease BRANCH stock (branch is sending back)
                        $branchBatch = Batch::where('shop_id', $branchShopId)
                            ->where('barcode', $saleItem->barcode)
                            ->lockForUpdate()
                            ->first();

                        abort_if(!$branchBatch, 422, "Branch batch not found for barcode {$saleItem->barcode}.");
                        abort_if((int)$branchBatch->quantity < $qty, 422, "Branch stock insufficient for barcode {$saleItem->barcode}.");

                        $branchBatch->quantity = (int)$branchBatch->quantity - $qty;
                        $branchBatch->save();

                        // 2) Increase MAIN stock back (recommended consistency)
                        $mainBatch = Batch::where('id', $saleItem->batch_id)
                            ->where('shop_id', $shop->id) // main shop
                            ->lockForUpdate()
                            ->first();

                        // if main batch exists, restore qty back
                        if ($mainBatch) {
                            $mainBatch->quantity = (int)$mainBatch->quantity + $qty;
                            $mainBatch->save();
                        }
                    } else {
                        // Normal customer return: restore stock to same shop batch
                        $batch = Batch::where('id', $saleItem->batch_id)
                            ->where('shop_id', $shop->id)
                            ->lockForUpdate()
                            ->first();

                        if ($batch) {
                            $batch->quantity = (int)$batch->quantity + $qty;
                            $batch->save();
                        }
                    }

                    // Refund amount uses sale item unit_price * qty (your existing behavior)
                    $unit = (float)$saleItem->unit_price;
                    $lineRefund = $unit * $qty;

                    SaleReturnItem::create([
                        'sale_return_id' => $returnHeader->id,
                        'sale_item_id' => $saleItem->id,
                        'batch_id' => $saleItem->batch_id,
                        'quantity' => $qty,
                        'unit_price' => round($unit, 2),
                        'line_refund' => round($lineRefund, 2),
                    ]);

                    $refundTotal += $lineRefund;
                }

                $refundTotal = round($refundTotal, 2);

                $returnHeader->refund_amount = $refundTotal;
                $returnHeader->save();

                // record refund in payments as negative
                Payment::create([
                    'shop_id' => $shop->id,
                    'sale_id' => $sale->id,
                    'method' => $data['method'],
                    'bank_id' => $data['method'] === 'bank' ? (int)$data['bank_id'] : null,
                    'amount' => -1 * $refundTotal,
                    'paid_at' => now(),
                ]);

                // Status update
                $totalSold = (int)$sale->items->sum('quantity');
                $totalReturnedNow = (int)SaleReturnItem::whereIn('sale_item_id', $sale->items->pluck('id'))
                    ->sum('quantity');

                if ($totalReturnedNow >= $totalSold) {
                    $sale->status = 'returned';
                } else {
                    $sale->status = 'partial_return';
                }
                $sale->save();

                return $returnHeader;
            });

            return response()->json([
                'ok' => true,
                'message' => 'Partial return processed.',
                'return_id' => $return->id,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Partial return failed: '.$e->getMessage(),
            ], 500);
        }
    }
}