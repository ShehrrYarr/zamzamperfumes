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
        abort_if(!auth()->check(), 403);

        $shopId = (int) (auth()->user()->shop_id ?? 0);
        abort_if(!$shopId, 403);

        $shop = Shop::find($shopId);
        abort_if(!$shop, 403);

        abort_if(!in_array($shop->type, ['main','branch'], true), 403);

        return $shop;
    }

    /**
     * GET /main/pos/sale/{sale}
     * GET /branch/pos/sale/{sale}
     *
     * IMPORTANT: No implicit model binding. $sale can be sale ID or receipt string
     * (you currently pass sale id from UI; this works reliably on live).
     */
    public function sale($sale)
    {
        $shop = $this->currentShopOrFail();

        $q = trim((string) $sale);

        // Query restricted to current shop (prevents 403 mismatch issues)
        $saleQuery = Sale::query()
            ->where('shop_id', $shop->id)
            ->with(['items', 'payments.bank']);

        // if numeric -> ID
        if (ctype_digit($q)) {
            $saleQuery->where('id', (int) $q);
        } else {
            // OPTIONAL: if later you search by receipt/invoice, add correct columns here
            $saleQuery->where(function($qq) use ($q){
                // Change/keep only the columns that exist in your DB
                if (\Schema::hasColumn('sales', 'receipt_no')) {
                    $qq->orWhere('receipt_no', $q);
                }
                if (\Schema::hasColumn('sales', 'invoice_no')) {
                    $qq->orWhere('invoice_no', $q);
                }
                if (\Schema::hasColumn('sales', 'sale_no')) {
                    $qq->orWhere('sale_no', $q);
                }
            });
        }

        $saleModel = $saleQuery->first();

        if (!$saleModel) {
            return response()->json([
                'ok' => false,
                'message' => 'Sale not found for this shop.',
            ], 404);
        }

        // Already returned qty per sale_item
        $returnedMap = SaleReturnItem::selectRaw('sale_item_id, SUM(quantity) as returned_qty')
            ->whereIn('sale_item_id', $saleModel->items->pluck('id'))
            ->groupBy('sale_item_id')
            ->pluck('returned_qty', 'sale_item_id');

        return response()->json([
            'ok' => true,
            'sale' => [
                'id' => $saleModel->id,
                'customer' => $saleModel->customer_name ?: 'Walk-in',
                'phone' => $saleModel->customer_phone,
                'created_at' => optional($saleModel->created_at)->format('Y-m-d H:i'),
                'total' => (float) $saleModel->grand_total,
                'payment_method' => $saleModel->payments->first()?->method ?? 'counter',
                'bank_id' => $saleModel->payments->first()?->bank_id,
            ],
            'items' => $saleModel->items->map(function($it) use ($returnedMap){
                $returned  = (int) ($returnedMap[$it->id] ?? 0);
                $remaining = max(0, (int) $it->quantity - $returned);

                return [
                    'sale_item_id'    => $it->id,
                    'batch_id'        => $it->batch_id,
                    'name'            => $it->item_name,
                    'barcode'         => $it->barcode,
                    'sold_qty'        => (int) $it->quantity,
                    'returned_qty'    => $returned,
                    'returnable_qty'  => $remaining,
                    'unit_price'      => (float) $it->unit_price,
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
            'method'  => ['required','in:counter,bank'],
            'bank_id' => ['nullable','integer'],
            'items'   => ['required','array','min:1'],
            'items.*.sale_item_id' => ['required','integer'],
            'items.*.qty'          => ['required','integer','min:1'],
        ]);

        try {
            $return = DB::transaction(function () use ($shop, $user, $data) {

                $sale = Sale::with(['items','payments'])
                    ->where('id', (int)$data['sale_id'])
                    ->where('shop_id', $shop->id)
                    ->lockForUpdate()
                    ->firstOrFail();

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
                    'shop_id'        => $shop->id,
                    'sale_id'        => $sale->id,
                    'user_id'        => $user->id,
                    'refund_amount'  => 0,
                    'method'         => $data['method'],
                    'bank_id'        => $data['method'] === 'bank' ? (int)$data['bank_id'] : null,
                ]);

                $saleItemsById = $sale->items->keyBy('id');

                foreach ($data['items'] as $row) {
                    $saleItemId = (int)$row['sale_item_id'];
                    $qty        = (int)$row['qty'];

                    $saleItem = $saleItemsById->get($saleItemId);
                    if (!$saleItem) abort(422, 'Invalid sale item.');

                    $alreadyReturned = (int)($returnedMap[$saleItemId] ?? 0);
                    $returnable      = max(0, (int)$saleItem->quantity - $alreadyReturned);

                    if ($qty > $returnable) {
                        abort(422, "Return qty exceeds allowed for {$saleItem->barcode}.");
                    }

                    // restore stock for the batch
                    $batch = Batch::where('id', $saleItem->batch_id)
                        ->where('shop_id', $shop->id)
                        ->lockForUpdate()
                        ->first();

                    if ($batch) {
                        $batch->increment('quantity', $qty);
                    }

                    $unit       = (float)$saleItem->unit_price;
                    $lineRefund = $unit * $qty;

                    SaleReturnItem::create([
                        'sale_return_id' => $returnHeader->id,
                        'sale_item_id'   => $saleItem->id,
                        'batch_id'       => $saleItem->batch_id,
                        'quantity'       => $qty,
                        'unit_price'     => round($unit, 2),
                        'line_refund'    => round($lineRefund, 2),
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
                    'method'  => $data['method'],
                    'bank_id' => $data['method'] === 'bank' ? (int)$data['bank_id'] : null,
                    'amount'  => -1 * $refundTotal,
                    'paid_at' => now(),
                ]);

                // If fully returned, mark sale returned else partial_return
                $totalSold = (int)$sale->items->sum('quantity');
                $totalReturnedNow = (int) SaleReturnItem::whereIn('sale_item_id', $sale->items->pluck('id'))->sum('quantity');

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
