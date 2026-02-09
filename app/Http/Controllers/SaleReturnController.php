<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleReturn;


class SaleReturnController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function showReturnForm($saleId)
{
    $sale = \App\Models\Sale::with('items.batch.accessory')->findOrFail($saleId);
    return view('sales.return_form', compact('sale'));
}

// public function processReturn(Request $request, $saleId)
// {
//     $sale = \App\Models\Sale::with('items')->findOrFail($saleId);

//     $returnQty = $request->input('return_qty', []);
//     $hasReturn = false;

//     \DB::beginTransaction();
//     try {
//         foreach ($sale->items as $item) {
//             $qty = isset($returnQty[$item->id]) ? intval($returnQty[$item->id]) : 0;
//             if ($qty > 0 && $qty <= $item->quantity) {
//                 $hasReturn = true;

//                 // Update stock
//                 $batch = $item->batch;
//                 $batch->qty_remaining += $qty;
//                 $batch->save();

//                 // Log the return in a new SaleReturn table
//                 \App\Models\SaleReturn::create([
//                     'sale_id' => $sale->id,
//                     'sale_item_id' => $item->id,
//                     'quantity' => $qty,
//                     'returned_at' => now(),
//                     'returned_by' => auth()->id(),
//                 ]);
//             }
//         }

//         \DB::commit();

//         if ($hasReturn) {
//             return redirect()->route('sales.index')->with('success', 'Return processed successfully!');
//         } else {
//             return back()->with('danger', 'No items selected for return.');
//         }
//     } catch (\Exception $e) {
//         \DB::rollBack();
//         return back()->with('danger', 'Error processing return: ' . $e->getMessage());
//     }
// }
public function processReturn(Request $request, $saleId)
{
    // Load sale + items + batches so we can compute discount & stock
    $sale = \App\Models\Sale::with(['items.batch'])->findOrFail($saleId);

    $returnQtyInput = $request->input('return_qty', []);

    if (!is_array($returnQtyInput) || empty(array_filter($returnQtyInput))) {
        return response()->json([
            'success' => false,
            'message' => 'No items selected for return.',
        ], 422);
    }

    \DB::beginTransaction();
    try {
        // 1) Gross subtotal and discount at sale level
        $grossSubtotal = $sale->items->sum('subtotal');
        $discountTotal = (float) ($sale->discount_amount ?? 0);

        // 2) Create a SaleReturn header record (one per return operation)
        $saleReturn = \App\Models\SaleReturn::create([
            'sale_id' => $sale->id,
            'user_id' => auth()->id(),
            'reason'  => null,
        ]);

        $hasReturn   = false;
        $totalRefund = 0.0;

        foreach ($sale->items as $item) {
            $requestedQty = isset($returnQtyInput[$item->id])
                ? (int)$returnQtyInput[$item->id]
                : 0;

            if ($requestedQty <= 0) {
                continue;
            }

            // 3) Don't allow returning more than sold (taking prior returns into account)
            $alreadyReturned = \App\Models\SaleReturnItem::where('sale_item_id', $item->id)
                ->sum('quantity');

            $maxReturnable = $item->quantity - $alreadyReturned;

            if ($maxReturnable <= 0) {
                continue; // nothing left to return for this line
            }

            if ($requestedQty > $maxReturnable) {
                throw new \Exception(
                    "Return quantity for item #{$item->id} exceeds remaining sale quantity. " .
                    "Max allowed: {$maxReturnable}"
                );
            }

            // 4) Work out effective unit price AFTER discount (discount prorated by line subtotal)
            $lineSubtotal = (float) $item->subtotal;
            $lineDiscount = 0.0;

            if ($grossSubtotal > 0 && $discountTotal > 0 && $lineSubtotal > 0) {
                $ratio       = $lineSubtotal / $grossSubtotal;           // this line's share of the sale
                $lineDiscount = $discountTotal * $ratio;                 // total discount allocated to this line
            }

            // price after discount (for this line)
            $effectiveUnit = $item->quantity > 0
                ? ($lineSubtotal - $lineDiscount) / $item->quantity
                : (float) $item->price_per_unit;

            // 5) Update stock back to batch
            $batch = $item->batch;
            if ($batch) {
                $batch->qty_remaining += $requestedQty;
                $batch->save();
            }

            // 6) Log return item with discounted unit price
            \App\Models\SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'sale_item_id'   => $item->id,
                'quantity'       => $requestedQty,
                'price_per_unit' => $effectiveUnit,
            ]);

            $hasReturn   = true;
            $totalRefund += $effectiveUnit * $requestedQty;
        }

        if (!$hasReturn) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No items selected for return.',
            ], 422);
        }

        // If your sale_returns table has a total_refund column, you can store it:
        // $saleReturn->update(['total_refund' => $totalRefund]);

        \DB::commit();

        return response()->json([
            'success'       => true,
            'message'       => 'Return processed successfully!',
            'refund_amount' => round($totalRefund, 2),
        ]);

    } catch (\Throwable $e) {
        \DB::rollBack();
        \Log::error('Error processing return: '.$e->getMessage(), [
            'sale_id' => $saleId,
            'trace'   => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error processing return: '.$e->getMessage(),
        ], 500);
    }
}


}
