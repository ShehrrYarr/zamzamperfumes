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

class ReturnController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop, 403);
        abort_if(!in_array($shop->type, ['main','branch'], true), 403);
        return $shop;
    }

    public function process(Request $request)
    {
        $shop = $this->currentShopOrFail();
        $user = auth()->user();

        $data = $request->validate([
            'sale_id' => ['required','integer'],
            'method'  => ['nullable','in:counter,bank'], // optional
            'bank_id' => ['nullable','integer'],
        ]);

        try {
            $return = DB::transaction(function () use ($shop, $user, $data) {

                $sale = Sale::with(['items','payments'])
                    ->where('id', $data['sale_id'])
                    ->where('shop_id', $shop->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Prevent full return if already fully returned
                if ($sale->status === 'returned') {
                    abort(422, 'This sale is already fully returned.');
                }

                // Refund method default = original payment method
                $origPay = $sale->payments->first();
                $method = $data['method'] ?? ($origPay?->method ?? 'counter');
                $bankId = $method === 'bank'
                    ? (int)($data['bank_id'] ?? $origPay?->bank_id)
                    : null;

                if ($method === 'bank' && empty($bankId)) {
                    abort(422, 'Please select a bank for bank refund.');
                }

                // Create return header
                $returnHeader = SaleReturn::create([
                    'shop_id' => $shop->id,
                    'sale_id' => $sale->id,
                    'user_id' => $user->id,
                    'refund_amount' => 0,
                    'method' => $method,
                    'bank_id' => $bankId,
                ]);

                $refundTotal = 0.0;

                foreach ($sale->items as $it) {
                    // Return FULL remaining qty for each item
                    $qty = (int)$it->quantity;

                    // Restore stock
                    $batch = Batch::where('id', $it->batch_id)
                        ->where('shop_id', $shop->id)
                        ->lockForUpdate()
                        ->first();

                    if ($batch) {
                        $batch->quantity = (int)$batch->quantity + $qty;
                        $batch->save();
                    }

                    $unit = (float)$it->unit_price;
                    $lineRefund = $unit * $qty;

                    SaleReturnItem::create([
                        'sale_return_id' => $returnHeader->id,
                        'sale_item_id'   => $it->id,
                        'batch_id'       => $it->batch_id,
                        'quantity'       => $qty,
                        'unit_price'     => round($unit, 2),
                        'line_refund'    => round($lineRefund, 2),
                    ]);

                    $refundTotal += $lineRefund;
                }

                $refundTotal = round($refundTotal, 2);

                $returnHeader->refund_amount = $refundTotal;
                $returnHeader->save();

                // Record refund payment negative
                Payment::create([
                    'shop_id' => $shop->id,
                    'sale_id' => $sale->id,
                    'method'  => $method,
                    'bank_id' => $bankId,
                    'amount'  => -1 * $refundTotal,
                    'paid_at' => now(),
                ]);

                // Mark sale as fully returned
                $sale->status = 'returned';
                $sale->save();

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
                'message' => 'Return failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
