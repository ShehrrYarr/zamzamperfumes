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

   public function sales(Request $request)
{
    $this->assertAdmin();

    $shops = Shop::orderBy('type')->orderBy('name')->get();

    // Base sales query (no joins that cause GROUP BY issues)
    $salesQ = Sale::query()
        ->with(['shop', 'user']) // items are loaded after pagination to avoid heavy joins
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
    if ($request->filled('status')) {
        $salesQ->where('sales.status', $request->status);
    }

    // ✅ STRICT-SAFE cost subquery grouped by sale_id
    $costSub = DB::table('sale_items')
        ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
        ->join('sales as s', 's.id', '=', 'sale_items.sale_id')
        ->whereColumn('batches.shop_id', 's.shop_id') // safety
        ->selectRaw('sale_items.sale_id as sale_id')
        ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
        ->groupBy('sale_items.sale_id');

    // Apply SAME filters to the cost subquery via joined sales alias "s"
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
    }

    // Join costSub to sales list
    $q = (clone $salesQ)
        ->leftJoinSub($costSub, 'c', function ($join) {
            $join->on('c.sale_id', '=', 'sales.id');
        })
        ->select('sales.*')
        ->selectRaw('COALESCE(c.cost_total,0) as cost_total');

    $sales = $q->paginate(25)->withQueryString();

    // ✅ Load items for dropdown (after pagination, so we don't break strict SQL)
    $sales->getCollection()->load(['items']); // Sale::items (SaleItem)

    // ✅ STRICT-SAFE totals (no GROUP BY / no DISTINCT hacks)
    $totalsBase = (clone $salesQ)->reorder();

    $totals = (object)[
        'sales_count'   => (int) (clone $totalsBase)->count(),
        'revenue_total' => (float) (clone $totalsBase)->sum('grand_total'),
        'cost_total'    => 0.0,
    ];

    // Total cost across filtered sales (apply same filters through joining sales)
    $costTotalQ = DB::table('sale_items')
        ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->whereColumn('batches.shop_id', 'sales.shop_id');

    if ($request->filled('shop_id')) {
        $costTotalQ->where('sales.shop_id', (int)$request->shop_id);
    }
    if ($request->filled('from')) {
        $costTotalQ->whereDate('sales.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $costTotalQ->whereDate('sales.created_at', '<=', $request->to);
    }
    if ($request->filled('status')) {
        $costTotalQ->where('sales.status', $request->status);
    }

    $totals->cost_total = (float) $costTotalQ
        ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
        ->value('cost_total');

    $profit = (float)$totals->revenue_total - (float)$totals->cost_total;

    return view('admin.reports.sales', compact('sales', 'totals', 'profit', 'shops'));
}


   public function returns(Request $request)
{
    $this->assertAdmin();

    $shops = Shop::orderBy('type')->orderBy('name')->get();

    // Base returns query (no joins that cause GROUP BY issues)
    $returnsQ = SaleReturn::query()
        ->with([
            'shop',
            'user',
            'sale',

            // ✅ NEW: items of this return + fallback relations for display
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

    // ✅ STRICT-SAFE totals
    $totalsBase = (clone $returnsQ)->reorder();

    $totals = (object)[
        'return_count'      => (int)(clone $totalsBase)->count(),
        'refund_total'      => (float)(clone $totalsBase)->sum('refund_amount'),
        'return_cost_total' => 0.0,
    ];

    // Total return cost across filtered returns
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
