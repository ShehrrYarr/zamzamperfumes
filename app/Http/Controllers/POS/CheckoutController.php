<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckoutController extends Controller
{
    private function currentShopOrFail(): Shop
    {
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop, 403);
        abort_if(!in_array($shop->type, ['main','branch'], true), 403);
        return $shop;
    }

    private function cartKey(int $shopId): string
    {
        return "pos_cart_shop_{$shopId}";
    }

    public function checkout(Request $request)
    {
        $shop = $this->currentShopOrFail();
        $user = auth()->user();

        $data = $request->validate([
            'customer_name'  => ['nullable','string','max:255'],
            'customer_phone' => ['nullable','string','max:30'],

            'discount_type'  => ['required','in:none,flat,percent'],
            'discount_value' => ['required','numeric','min:0'],

            'payment_method' => ['required','in:counter,bank'],
            'bank_id'        => ['nullable','integer'],
        ]);

        // Walk-in behavior
        $customerName  = trim((string)($data['customer_name'] ?? ''));
        $customerPhone = trim((string)($data['customer_phone'] ?? ''));

        if ($customerName === '')  $customerName = null;
        if ($customerPhone === '') $customerPhone = null;

        $cart = session()->get($this->cartKey($shop->id), []);
        $cartLines = array_values($cart);

        if (count($cartLines) === 0) {
            return response()->json(['ok' => false, 'message' => 'Cart is empty.'], 422);
        }

        // bank validation if bank method
        if ($data['payment_method'] === 'bank') {
            if (empty($data['bank_id'])) {
                return response()->json(['ok' => false, 'message' => 'Please select a bank.'], 422);
            }

            $bank = Bank::where('id', $data['bank_id'])
                ->where('shop_id', $shop->id)
                ->where('is_active', true)
                ->first();

            if (!$bank) {
                return response()->json(['ok' => false, 'message' => 'Invalid bank.'], 422);
            }
        }

        try {
            $sale = DB::transaction(function () use (
                $shop, $user, $data, $customerName, $customerPhone, $cartLines
            ) {
                // 1) Re-check stock with locks, calculate subtotal
                $subtotal = 0.0;

                // We'll lock each batch row to prevent race conditions
                $lockedBatches = []; // [batchId => [$batch, $unitPrice, $lineTotal, $qty]]

                foreach ($cartLines as $line) {
                    $batchId = (int)($line['batch_id'] ?? 0);
                    $qty     = (int)($line['qty'] ?? 0);

                    abort_if($batchId <= 0, 422, 'Invalid item in cart.');
                    abort_if($qty < 1, 422, 'Invalid quantity.');

                    $batch = Batch::with('perfume')
                        ->where('id', $batchId)
                        ->where('shop_id', $shop->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ((int)$batch->quantity < $qty) {
                        abort(422, "Insufficient stock for barcode {$batch->barcode}.");
                    }

                    // ✅ USE EDITABLE CART PRICE (fallback to batch sell_price)
                    $rawCartPrice = $line['price'] ?? null;

                    if ($rawCartPrice === null || $rawCartPrice === '') {
                        $unitPrice = (float)($batch->sell_price ?? 0);
                    } else {
                        // validate numeric & non-negative
                        if (!is_numeric($rawCartPrice)) {
                            abort(422, "Invalid price for barcode {$batch->barcode}.");
                        }
                        $unitPrice = (float)$rawCartPrice;
                        if ($unitPrice < 0) {
                            abort(422, "Invalid price for barcode {$batch->barcode}.");
                        }
                    }

                    $lineTotal = $unitPrice * $qty;
                    $subtotal += $lineTotal;

                    $lockedBatches[$batchId] = [$batch, $unitPrice, $lineTotal, $qty];
                }

                // 2) Discount compute
                $discountType  = $data['discount_type'];
                $discountValue = (float)$data['discount_value'];
                $discountAmount = 0.0;

                if ($discountType === 'flat') {
                    $discountAmount = min(max($discountValue, 0), $subtotal);
                } elseif ($discountType === 'percent') {
                    $pct = min(max($discountValue, 0), 100);
                    $discountAmount = min(($subtotal * $pct / 100), $subtotal);
                }

                $grandTotal = max($subtotal - $discountAmount, 0);

                // 3) Create sale
                $sale = Sale::create([
                    'shop_id'         => $shop->id,
                    'user_id'         => $user->id,
                    'customer_name'   => $customerName,
                    'customer_phone'  => $customerPhone,
                    'subtotal'        => round($subtotal, 2),
                    'discount_type'   => $discountType,
                    'discount_value'  => round($discountValue, 2),
                    'discount_amount' => round($discountAmount, 2),
                    'grand_total'     => round($grandTotal, 2),
                    'status'          => 'completed',
                ]);

                // 4) Create items + deduct stock
                foreach ($lockedBatches as $batchId => $arr) {
                    /** @var \App\Models\Batch $batch */
                    [$batch, $unitPrice, $lineTotal, $qty] = $arr;

                    SaleItem::create([
                        'sale_id'            => $sale->id,
                        'batch_id'           => $batch->id,
                        'barcode'            => $batch->barcode,
                        'item_name'          => $batch->perfume?->name ?? ('Batch#'.$batch->id),
                        'unit_price'         => round($unitPrice, 2),   // ✅ editable price saved
                        'quantity'           => $qty,
                        'original_quantity'  => $qty,
                        'line_total'         => round($lineTotal, 2),   // ✅ editable price total
                    ]);

                    $batch->quantity = (int)$batch->quantity - (int)$qty;
                    $batch->save();
                }

                // 5) Payment record
                Payment::create([
                    'shop_id' => $shop->id,
                    'sale_id' => $sale->id,
                    'method'  => $data['payment_method'],
                    'bank_id' => $data['payment_method'] === 'bank' ? (int)$data['bank_id'] : null,
                    'amount'  => round($grandTotal, 2),
                    'paid_at' => now(),
                ]);

                return $sale;
            });

            // Clear cart only if everything succeeded
            session()->forget($this->cartKey($shop->id));

            return response()->json([
                'ok'          => true,
                'sale_id'     => $sale->id,
                'message'     => 'Sale completed.',
                'receipt_url' => route(
                    auth()->user()->role === 'main_shop' ? 'main.pos.receipt' : 'branch.pos.receipt',
                    $sale->id
                ),
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Checkout failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}