<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\vendor;
use Carbon\Carbon;

class SalesLiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

 public function index(Request $request)
    {
        // Default: Today 00:00 → 23:59
        $today = Carbon::today(); // uses app timezone
        $start = $request->query('start_date', $today->copy()->format('Y-m-d'));
        $end   = $request->query('end_date',   $today->copy()->format('Y-m-d'));
        $vendorId = $request->query('vendor_id');

        $vendors = Vendor::orderBy('name')->get(['id','name']);

        return view('sales.live', compact('vendors', 'start', 'end', 'vendorId'));
    }

//     public function feed(Request $request)
// {
//     // Validate inputs
//     $request->validate([
//         'start_date' => 'required|date_format:Y-m-d',
//         'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
//         'vendor_id'  => 'nullable|exists:vendors,id',
//     ]);

//     // Build concrete datetime range (00:00:00 to 23:59:59)
//     $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_date.' 00:00:00');
//     $end   = Carbon::createFromFormat('Y-m-d H:i:s', $request->end_date.' 23:59:59');

//     $vendorId = $request->vendor_id;

//     $sales = Sale::with([
//             'vendor:id,name',
//             'user:id,name',
//             'payments.bank:id,name',
//             // we need accessory_batch_id + prices to compute profit
//             'items' => function ($q) {
//                 $q->select(
//                     'id',
//                     'sale_id',
//                     'accessory_batch_id',   // <-- important for batch relation
//                     'quantity',
//                     'price_per_unit',
//                     'subtotal'
//                 );
//             },
//             // load batch so we can read purchase_price
//             'items.batch', // no custom select needed; default is fine
//         ])
//         ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
//         ->whereBetween('sale_date', [$start, $end])
//         ->orderByDesc('sale_date')
//         ->limit(300) // keep it snappy; adjust as needed
//         ->get();

//     // Build lightweight payload
//     $rows = $sales->map(function ($s) {
//         $subtotal = $s->items->sum('subtotal');
//         $discount = (float) ($s->discount_amount ?? 0);
//         $net      = max($subtotal - $discount, 0);

//         // 🔹 Gross margin before discount = (sell − cost) * qty
//         $grossMargin = $s->items->sum(function ($item) {
//             $cost = optional($item->batch)->purchase_price ?? 0;
//             return ($item->price_per_unit - $cost) * $item->quantity;
//         });

//         // 🔹 Final profit after discount
//         $profit = $grossMargin - $discount;

//         $payments = $s->payments->map(function($p){
//             return [
//                 'method'  => $p->method,
//                 'bank'    => optional($p->bank)->name,
//                 'amount'  => (float)$p->amount,
//                 'ref'     => $p->reference_no,
//                 'paid_at' => optional($p->paid_at)->format('Y-m-d H:i'),
//             ];
//         });

//         return [
//             'id'                   => $s->id,
//             'sale_date'            => optional($s->sale_date)->format('Y-m-d H:i'),
//             'who'                  => $s->vendor ? ('Vendor: '.$s->vendor->name) : ($s->customer_name ? ('Customer: '.$s->customer_name) : 'Walk-in'),
//             'total'                => (float)$net,
//             'subtotal'             => (float)$subtotal,
//             'discount'             => (float)$discount,
//             'status'               => $s->status,
//             'user'                 => optional($s->user)->name,
//             'comment'              => $s->comment,
//             'items_count'          => $s->items->count(),
//             'payments'             => $payments,
//             'invoice_url'          => route('sales.invoice', $s->id),

//             // 🔹 profit numbers we can show on UI
//             'profit'               => round($profit, 2),
//             'profit_before_discount' => round($grossMargin, 2),
//         ];
//     });

//     // Totals
//     $totals = [
//         'count'       => $rows->count(),
//         'net_sum'     => round($rows->sum('total'), 2),
//         'profit_sum'  => round($rows->sum('profit'), 2), // 🔹 new
//     ];

//     return response()->json([
//         'success'      => true,
//         'data'         => $rows,
//         'totals'       => $totals,
//         'refreshed_at' => now()->format('H:i:s'),
//     ]);
// }
public function feed(Request $request)
{
    // Validate inputs
    $request->validate([
        'start_date' => 'required|date_format:Y-m-d',
        'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        'vendor_id'  => 'nullable|exists:vendors,id',
    ]);

    // Build concrete datetime range (00:00:00 to 23:59:59)
    $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->start_date . ' 00:00:00');
    $end   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->end_date   . ' 23:59:59');

    $vendorId = $request->vendor_id;

    // 1) Load sales with items + batches + payments
    $sales = \App\Models\Sale::with([
            'vendor:id,name',
            'user:id,name',
            'payments.bank:id,name',
            'items' => function ($q) {
                $q->select(
                    'id',
                    'sale_id',
                    'accessory_batch_id',
                    'quantity',
                    'price_per_unit',
                    'subtotal'
                );
            },
            'items.batch:id,purchase_price'
        ])
        ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
        ->whereBetween('sale_date', [$start, $end])
        ->orderByDesc('sale_date')
        ->limit(300)
        ->get();

    if ($sales->isEmpty()) {
        return response()->json([
            'success'      => true,
            'data'         => [],
            'totals'       => ['count' => 0, 'net_sum' => 0.00, 'profit_sum' => 0.00],
            'refreshed_at' => now()->format('H:i:s'),
        ]);
    }

    // 2) Get total returned qty per sale_item_id for these sales
    $saleItemIds = $sales->flatMap->items->pluck('id');

    $returnsByItem = \DB::table('sale_return_items')
        ->select('sale_item_id', \DB::raw('SUM(quantity) as returned_qty'))
        ->whereIn('sale_item_id', $saleItemIds)
        ->groupBy('sale_item_id')
        ->pluck('returned_qty', 'sale_item_id');   // [sale_item_id => returned_qty]

    // 3) Build payload rows with profit based on kept quantity
    $rows = $sales->map(function ($s) use ($returnsByItem) {

        $discount = (float) ($s->discount_amount ?? 0);

        $grossSubtotalSold = 0.0;   // subtotal for all sold qty (before discount)
        $grossSubtotalKept = 0.0;   // subtotal for non-returned qty
        $grossMarginKept   = 0.0;   // margin for non-returned qty

        foreach ($s->items as $item) {
            $soldQty      = (int) $item->quantity;
            $returnedQty  = (int) ($returnsByItem[$item->id] ?? 0);
            $keptQty      = max($soldQty - $returnedQty, 0);

            $unitSell = (float) $item->price_per_unit;
            $cost     = (float) (optional($item->batch)->purchase_price ?? 0);

            $lineSubtotalSold = $unitSell * $soldQty;
            $lineSubtotalKept = $unitSell * $keptQty;

            $grossSubtotalSold += $lineSubtotalSold;
            $grossSubtotalKept += $lineSubtotalKept;

            $grossMarginKept   += ($unitSell - $cost) * $keptQty;
        }

        // Allocate discount only on kept quantity
        $discountOnKept = 0.0;
        if ($discount > 0 && $grossSubtotalSold > 0 && $grossSubtotalKept > 0) {
            $discountOnKept = $discount * ($grossSubtotalKept / $grossSubtotalSold);
        }

        // Net AFTER discount and returns
        $net = $grossSubtotalKept - $discountOnKept;
        if ($net < 0) $net = 0; // safety

        // Profit = margin on kept qty − discount on kept qty
        $profit = $grossMarginKept - $discountOnKept;

        $payments = $s->payments->map(function($p){
            return [
                'method'  => $p->method,
                'bank'    => optional($p->bank)->name,
                'amount'  => (float) $p->amount,
                'ref'     => $p->reference_no,
                'paid_at' => optional($p->paid_at)->format('Y-m-d H:i'),
            ];
        });

        return [
            'id'                     => $s->id,
            'sale_date'              => optional($s->sale_date)->format('Y-m-d H:i'),
            'who'                    => $s->vendor
                                            ? ('Vendor: '.$s->vendor->name)
                                            : ($s->customer_name
                                                ? ('Customer: '.$s->customer_name)
                                                : 'Walk-in'),
            'total'                  => round($net, 2),           // net after discount + returns
            'subtotal'               => round($grossSubtotalSold, 2), // full sold before discount
            'discount'               => round($discount, 2),
            'status'                 => $s->status,
            'user'                   => optional($s->user)->name,
            'comment'                => $s->comment,
            'items_count'            => $s->items->count(),
            'payments'               => $payments,
            'invoice_url'            => route('sales.invoice', $s->id),

            'profit'                 => round($profit, 2),
            'profit_before_discount' => round($grossMarginKept, 2),
        ];
    });

    // 4) Totals for header cards
    $totals = [
        'count'      => $rows->count(),
        'net_sum'    => round($rows->sum('total'), 2),
        'profit_sum' => round($rows->sum('profit'), 2),
    ];

    return response()->json([
        'success'      => true,
        'data'         => $rows,
        'totals'       => $totals,
        'refreshed_at' => now()->format('H:i:s'),
    ]);
}


}
