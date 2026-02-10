<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\Sale;
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

        $data = $request->validate([
            'sale_id' => ['required','integer'],
        ]);

        try {
            $sale = DB::transaction(function () use ($shop, $data) {

                /** @var Sale $sale */
                $sale = Sale::with(['items', 'payments'])
                    ->where('id', $data['sale_id'])
                    ->where('shop_id', $shop->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($sale->status === 'returned') {
                    abort(422, 'This sale is already returned.');
                }

                // Restore stock (lock batches)
                foreach ($sale->items as $it) {
                    $batch = Batch::where('id', $it->batch_id)
                        ->where('shop_id', $shop->id)
                        ->lockForUpdate()
                        ->first();

                    // If batch deleted for some reason, we still allow marking return
                    if ($batch) {
                        $batch->quantity = (int)$batch->quantity + (int)$it->quantity;
                        $batch->save();
                    }
                }

                // Determine original payment method (first payment)
                $origPay = $sale->payments->first();
                $method = $origPay?->method ?? 'counter';
                $bankId = $method === 'bank' ? ($origPay?->bank_id) : null;

                // Create refund payment (negative)
                Payment::create([
                    'shop_id' => $shop->id,
                    'sale_id' => $sale->id,
                    'method' => $method,
                    'bank_id' => $bankId,
                    'amount' => -1 * (float)$sale->grand_total,
                    'paid_at' => now(),
                ]);

                // Mark sale returned
                $sale->status = 'returned';
                $sale->save();

                return $sale;
            });

            return response()->json([
                'ok' => true,
                'message' => 'Return processed.',
                'sale_id' => $sale->id,
                'return_receipt_url' => route(
                    auth()->user()->role === 'main_shop' ? 'main.pos.return_receipt' : 'branch.pos.return_receipt',
                    $sale->id
                ),
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Return failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
