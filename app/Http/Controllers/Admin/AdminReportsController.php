<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportsController extends Controller
{
    private function assertAdmin(): void
    {
        abort_if(!auth()->check(), 403);
        abort_if(auth()->user()->role !== 'admin', 403);
    }

    public function batches(Request $request)
    {
        $this->assertAdmin();

        $shops = Shop::orderBy('type')->orderBy('name')->get();

        $q = Batch::query()
            ->with(['perfume', 'shop'])
            ->orderByDesc('id');

        if ($request->filled('shop_id')) {
            $q->where('shop_id', (int)$request->shop_id);
        }
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('q')) {
            $s = trim($request->q);
            $q->where(function ($qq) use ($s) {
                $qq->where('barcode', 'like', "%{$s}%")
                    ->orWhere('batch_code', 'like', "%{$s}%"); // if you have
            });
        }

        // ✅ STRICT-SAFE totals: do NOT use orderBy/limit with aggregates
        $totals = (clone $q)
            ->reorder()
            ->selectRaw('COUNT(*) as batch_count')
            ->selectRaw('COALESCE(SUM(quantity),0) as total_qty')
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(cost_price,0)),0) as total_stock_cost')
            ->first();

        $batches = $q->paginate(25)->withQueryString();

        return view('admin.reports.batches', compact('batches', 'totals', 'shops'));
    }

//    public function sales(Request $request)
// {
//     $this->assertAdmin();

//     $shops = Shop::orderBy('type')->orderBy('name')->get();

//     // Base sales query (no joins that cause GROUP BY issues)
//     $salesQ = Sale::query()
//         ->with(['shop', 'user']) // items are loaded after pagination to avoid heavy joins
//         ->orderByDesc('sales.id');

//     if ($request->filled('shop_id')) {
//         $salesQ->where('sales.shop_id', (int)$request->shop_id);
//     }
//     if ($request->filled('from')) {
//         $salesQ->whereDate('sales.created_at', '>=', $request->from);
//     }
//     if ($request->filled('to')) {
//         $salesQ->whereDate('sales.created_at', '<=', $request->to);
//     }
//     if ($request->filled('status')) {
//         $salesQ->where('sales.status', $request->status);
//     }

//     // ✅ STRICT-SAFE cost subquery grouped by sale_id
//     $costSub = DB::table('sale_items')
//         ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
//         ->join('sales as s', 's.id', '=', 'sale_items.sale_id')
//         ->whereColumn('batches.shop_id', 's.shop_id') // safety
//         ->selectRaw('sale_items.sale_id as sale_id')
//         ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
//         ->groupBy('sale_items.sale_id');

//     // Apply SAME filters to the cost subquery via joined sales alias "s"
//     if ($request->filled('shop_id')) {
//         $costSub->where('s.shop_id', (int)$request->shop_id);
//     }
//     if ($request->filled('from')) {
//         $costSub->whereDate('s.created_at', '>=', $request->from);
//     }
//     if ($request->filled('to')) {
//         $costSub->whereDate('s.created_at', '<=', $request->to);
//     }
//     if ($request->filled('status')) {
//         $costSub->where('s.status', $request->status);
//     }

//     // Join costSub to sales list
//     $q = (clone $salesQ)
//         ->leftJoinSub($costSub, 'c', function ($join) {
//             $join->on('c.sale_id', '=', 'sales.id');
//         })
//         ->select('sales.*')
//         ->selectRaw('COALESCE(c.cost_total,0) as cost_total');

//     $sales = $q->paginate(25)->withQueryString();

//     // ✅ Load items for dropdown (after pagination, so we don't break strict SQL)
//     $sales->getCollection()->load(['items']); // Sale::items (SaleItem)

//     // ✅ STRICT-SAFE totals (no GROUP BY / no DISTINCT hacks)
//     $totalsBase = (clone $salesQ)->reorder();

//     $totals = (object)[
//         'sales_count'   => (int) (clone $totalsBase)->count(),
//         'revenue_total' => (float) (clone $totalsBase)->sum('grand_total'),
//         'cost_total'    => 0.0,
//     ];

//     // Total cost across filtered sales (apply same filters through joining sales)
//     $costTotalQ = DB::table('sale_items')
//         ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
//         ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
//         ->whereColumn('batches.shop_id', 'sales.shop_id');

//     if ($request->filled('shop_id')) {
//         $costTotalQ->where('sales.shop_id', (int)$request->shop_id);
//     }
//     if ($request->filled('from')) {
//         $costTotalQ->whereDate('sales.created_at', '>=', $request->from);
//     }
//     if ($request->filled('to')) {
//         $costTotalQ->whereDate('sales.created_at', '<=', $request->to);
//     }
//     if ($request->filled('status')) {
//         $costTotalQ->where('sales.status', $request->status);
//     }

//     $totals->cost_total = (float) $costTotalQ
//         ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
//         ->value('cost_total');

//     $profit = (float)$totals->revenue_total - (float)$totals->cost_total;

//     return view('admin.reports.sales', compact('sales', 'totals', 'profit', 'shops'));
// }


public function sales(Request $request)
{
    $this->assertAdmin();

    $shops = Shop::orderBy('type')->orderBy('name')->get();

    // Base sales query
    $salesQ = Sale::query()
        ->with(['shop', 'user'])
        ->orderByDesc('sales.id');

    if ($request->filled('shop_id')) {
        $salesQ->where('sales.shop_id', (int)$request->shop_id);
    }
    if ($request->filled('from')) {
        $salesQ->whereDate('sales.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $salesQ->whereDate('sales.created_at', '<=', $request->to);
    }

    // ✅ Default behavior: exclude fully returned sales unless filter is applied
    if ($request->filled('status')) {
        $salesQ->where('sales.status', $request->status);
    } else {
        $salesQ->whereIn('sales.status', ['completed', 'partial_return']);
    }

    // ✅ Sale type filter (customer vs internal_sale)
    if ($request->filled('sale_type')) {
        $st = $request->sale_type;

        if ($st === 'customer') {
            $salesQ->where(function ($qq) {
                $qq->whereNull('sales.sale_type')
                   ->orWhere('sales.sale_type', 'customer');
            });
        } else {
            $salesQ->where('sales.sale_type', $st);
        }
    }

    // ✅ STRICT-SAFE cost subquery grouped by sale_id
    $costSub = DB::table('sale_items')
        ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
        ->join('sales as s', 's.id', '=', 'sale_items.sale_id')
        ->whereColumn('batches.shop_id', 's.shop_id')
        ->selectRaw('sale_items.sale_id as sale_id')
        ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
        ->groupBy('sale_items.sale_id');

    // ✅ NEW: STRICT-SAFE qty subquery grouped by sale_id
    $qtySub = DB::table('sale_items')
        ->join('sales as s2', 's2.id', '=', 'sale_items.sale_id')
        ->selectRaw('sale_items.sale_id as sale_id')
        ->selectRaw('COALESCE(SUM(sale_items.quantity),0) as qty_total')
        ->groupBy('sale_items.sale_id');

    // Apply SAME filters to costSub through "s"
    if ($request->filled('shop_id')) {
        $costSub->where('s.shop_id', (int)$request->shop_id);
    }
    if ($request->filled('from')) {
        $costSub->whereDate('s.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $costSub->whereDate('s.created_at', '<=', $request->to);
    }
    if ($request->filled('status')) {
        $costSub->where('s.status', $request->status);
    } else {
        $costSub->whereIn('s.status', ['completed', 'partial_return']);
    }
    if ($request->filled('sale_type')) {
        $st = $request->sale_type;
        if ($st === 'customer') {
            $costSub->where(function ($qq) {
                $qq->whereNull('s.sale_type')
                   ->orWhere('s.sale_type', 'customer');
            });
        } else {
            $costSub->where('s.sale_type', $st);
        }
    }

    // Apply SAME filters to qtySub through "s2"
    if ($request->filled('shop_id')) {
        $qtySub->where('s2.shop_id', (int)$request->shop_id);
    }
    if ($request->filled('from')) {
        $qtySub->whereDate('s2.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $qtySub->whereDate('s2.created_at', '<=', $request->to);
    }
    if ($request->filled('status')) {
        $qtySub->where('s2.status', $request->status);
    } else {
        $qtySub->whereIn('s2.status', ['completed', 'partial_return']);
    }
    if ($request->filled('sale_type')) {
        $st = $request->sale_type;
        if ($st === 'customer') {
            $qtySub->where(function ($qq) {
                $qq->whereNull('s2.sale_type')
                   ->orWhere('s2.sale_type', 'customer');
            });
        } else {
            $qtySub->where('s2.sale_type', $st);
        }
    }

    // Join subs to sales list
    $q = (clone $salesQ)
        ->leftJoinSub($costSub, 'c', function ($join) {
            $join->on('c.sale_id', '=', 'sales.id');
        })
        ->leftJoinSub($qtySub, 'qt', function ($join) {
            $join->on('qt.sale_id', '=', 'sales.id');
        })
        ->select('sales.*')
        ->selectRaw('COALESCE(c.cost_total,0) as cost_total')
        ->selectRaw('COALESCE(qt.qty_total,0) as qty_total');

    $sales = $q->paginate(25)->withQueryString();

    // Load items after pagination (used by collapse dropdown)
    $sales->getCollection()->load(['items']);

    // ✅ STRICT-SAFE totals
    $totalsBase = (clone $salesQ)->reorder();

    $totals = (object)[
        'sales_count'   => (int)(clone $totalsBase)->count(),
        'revenue_total' => (float)(clone $totalsBase)->sum('grand_total'),
        'cost_total'    => 0.0,
        'qty_total'     => 0.0, // ✅ NEW
    ];

    // Total cost across filtered sales
    $costTotalQ = DB::table('sale_items')
        ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->whereColumn('batches.shop_id', 'sales.shop_id');

    // Total qty across filtered sales
    $qtyTotalQ = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id');

    if ($request->filled('shop_id')) {
        $costTotalQ->where('sales.shop_id', (int)$request->shop_id);
        $qtyTotalQ->where('sales.shop_id', (int)$request->shop_id);
    }
    if ($request->filled('from')) {
        $costTotalQ->whereDate('sales.created_at', '>=', $request->from);
        $qtyTotalQ->whereDate('sales.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $costTotalQ->whereDate('sales.created_at', '<=', $request->to);
        $qtyTotalQ->whereDate('sales.created_at', '<=', $request->to);
    }

    if ($request->filled('status')) {
        $costTotalQ->where('sales.status', $request->status);
        $qtyTotalQ->where('sales.status', $request->status);
    } else {
        $costTotalQ->whereIn('sales.status', ['completed', 'partial_return']);
        $qtyTotalQ->whereIn('sales.status', ['completed', 'partial_return']);
    }

    if ($request->filled('sale_type')) {
        $st = $request->sale_type;

        if ($st === 'customer') {
            $costTotalQ->where(function ($qq) {
                $qq->whereNull('sales.sale_type')
                   ->orWhere('sales.sale_type', 'customer');
            });
            $qtyTotalQ->where(function ($qq) {
                $qq->whereNull('sales.sale_type')
                   ->orWhere('sales.sale_type', 'customer');
            });
        } else {
            $costTotalQ->where('sales.sale_type', $st);
            $qtyTotalQ->where('sales.sale_type', $st);
        }
    }

    $totals->cost_total = (float)$costTotalQ
        ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
        ->value('cost_total');

    $totals->qty_total = (float)$qtyTotalQ
        ->selectRaw('COALESCE(SUM(sale_items.quantity),0) as qty_total')
        ->value('qty_total');

    $profit = (float)$totals->revenue_total - (float)$totals->cost_total;


    // ✅ Perfume-wise sold qty summary (based on SAME filters)
$perfumeSoldQ = DB::table('sale_items')
    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
    ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
    ->join('perfumes', 'perfumes.id', '=', 'batches.perfume_id')
    ->whereColumn('batches.shop_id', 'sales.shop_id') // strict-safe

    ->selectRaw('perfumes.id as perfume_id')
    ->selectRaw('perfumes.name as perfume_name')
    ->selectRaw('COALESCE(SUM(sale_items.quantity),0) as qty_sold')
    ->groupBy('perfumes.id', 'perfumes.name')
    ->orderByDesc('qty_sold');

// Apply SAME filters as your report
if ($request->filled('shop_id')) {
    $perfumeSoldQ->where('sales.shop_id', (int)$request->shop_id);
}
if ($request->filled('from')) {
    $perfumeSoldQ->whereDate('sales.created_at', '>=', $request->from);
}
if ($request->filled('to')) {
    $perfumeSoldQ->whereDate('sales.created_at', '<=', $request->to);
}

// Default status behavior (same as report)
if ($request->filled('status')) {
    $perfumeSoldQ->where('sales.status', $request->status);
} else {
    $perfumeSoldQ->whereIn('sales.status', ['completed', 'partial_return']);
}

// Same sale_type behavior (customer supports NULL)
if ($request->filled('sale_type')) {
    $st = $request->sale_type;
    if ($st === 'customer') {
        $perfumeSoldQ->where(function ($qq) {
            $qq->whereNull('sales.sale_type')
               ->orWhere('sales.sale_type', 'customer');
        });
    } else {
        $perfumeSoldQ->where('sales.sale_type', $st);
    }
}

$perfumeSold = $perfumeSoldQ->limit(200)->get();

   return view('admin.reports.sales', compact('sales', 'totals', 'profit', 'shops', 'perfumeSold'));
}

public function returns(Request $request)
{
    $this->assertAdmin();

    $shops = \App\Models\Shop::orderBy('type')->orderBy('name')->get();

    // Base returns query (no joins that cause GROUP BY issues)
    $returnsQ = \App\Models\SaleReturn::query()
        ->with([
            'shop',
            'user',
            'sale',

            // items of this return + fallback relations for display
            'items.saleItem',
            'items.batch.perfume',
        ])
        ->orderByDesc('sale_returns.id');

    if ($request->filled('shop_id')) {
        $returnsQ->where('sale_returns.shop_id', (int)$request->shop_id);
    }
    if ($request->filled('from')) {
        $returnsQ->whereDate('sale_returns.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $returnsQ->whereDate('sale_returns.created_at', '<=', $request->to);
    }
    if ($request->filled('method')) {
        $returnsQ->where('sale_returns.method', $request->method);
    }

    // ✅ STRICT-SAFE return cost subquery grouped by sale_return_id
    $returnCostSub = DB::table('sale_return_items')
        ->join('batches', 'batches.id', '=', 'sale_return_items.batch_id')
        ->join('sale_returns as r', 'r.id', '=', 'sale_return_items.sale_return_id')
        ->whereColumn('batches.shop_id', 'r.shop_id')
        ->selectRaw('sale_return_items.sale_return_id as rid')
        ->selectRaw('COALESCE(SUM(sale_return_items.quantity * COALESCE(batches.cost_price,0)),0) as return_cost_total')
        ->groupBy('sale_return_items.sale_return_id');

    $q = (clone $returnsQ)
        ->leftJoinSub($returnCostSub, 'rc', function ($join) {
            $join->on('rc.rid', '=', 'sale_returns.id');
        })
        ->select('sale_returns.*')
        ->selectRaw('COALESCE(rc.return_cost_total,0) as return_cost_total');

    $returns = $q->paginate(25)->withQueryString();

    // ✅ STRICT-SAFE totals (no GROUP BY / no DISTINCT hacks)
    $totalsBase = (clone $returnsQ)->reorder();

    $totals = (object)[
        'return_count'      => (int)(clone $totalsBase)->count(),
        'refund_total'      => (float)(clone $totalsBase)->sum('refund_amount'),
        'return_cost_total' => 0.0,
    ];

    // Total return cost across filtered returns (apply same filters through joining sale_returns)
    $returnCostTotalQ = DB::table('sale_return_items')
        ->join('batches', 'batches.id', '=', 'sale_return_items.batch_id')
        ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
        ->whereColumn('batches.shop_id', 'sale_returns.shop_id');

    if ($request->filled('shop_id')) {
        $returnCostTotalQ->where('sale_returns.shop_id', (int)$request->shop_id);
    }
    if ($request->filled('from')) {
        $returnCostTotalQ->whereDate('sale_returns.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $returnCostTotalQ->whereDate('sale_returns.created_at', '<=', $request->to);
    }
    if ($request->filled('method')) {
        $returnCostTotalQ->where('sale_returns.method', $request->method);
    }

    $totals->return_cost_total = (float)$returnCostTotalQ
        ->selectRaw('COALESCE(SUM(sale_return_items.quantity * COALESCE(batches.cost_price,0)),0) as return_cost_total')
        ->value('return_cost_total');

    return view('admin.reports.returns', compact('returns', 'totals', 'shops'));
}
}
