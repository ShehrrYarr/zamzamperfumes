<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Bank;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainReportsController extends Controller
{
    private function assertMain(): void
    {
        abort_if(!auth()->check(), 403);
        abort_if(auth()->user()->role !== 'main_shop', 403);
        abort_if(!auth()->user()->shop_id, 403);
    }

    public function batches(Request $request)
    {
        $this->assertMain();

        $shopId = (int)auth()->user()->shop_id;

        $q = Batch::query()
            ->with(['perfume', 'shop'])
            ->where('shop_id', $shopId)
            ->orderByDesc('id');

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
                   ->orWhere('batch_code', 'like', "%{$s}%");
            });
        }

        $totals = (clone $q)
            ->reorder()
            ->selectRaw('COUNT(*) as batch_count')
            ->selectRaw('COALESCE(SUM(quantity),0) as total_qty')
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(cost_price,0)),0) as total_stock_cost')
            ->first();

        $batches = $q->paginate(25)->withQueryString();

        return view('panels.main.reports.batches', compact('batches', 'totals'));
    }

    public function sales(Request $request)
    {
        $this->assertMain();

        $shopId = (int)auth()->user()->shop_id;

        // For filter dropdown
        $banks = Bank::query()
            ->where('shop_id', $shopId)
            ->orderBy('name')
            ->get();

        // Base sales query (no joins)
        $salesQ = Sale::query()
            ->with(['shop', 'user', 'bank'])
            ->where('sales.shop_id', $shopId)
            ->orderByDesc('sales.id');

        if ($request->filled('from')) {
            $salesQ->whereDate('sales.created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $salesQ->whereDate('sales.created_at', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $salesQ->where('sales.status', $request->status);
        }

        // ✅ New filters
        if ($request->filled('payment_method')) {
            // expects 'counter' or 'bank'
            $salesQ->where('sales.payment_method', $request->payment_method);
        }
        if ($request->filled('bank_id')) {
            $salesQ->where('sales.bank_id', (int)$request->bank_id);
        }

        // STRICT-safe cost subquery per sale_id
        $costSub = DB::table('sale_items')
            ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
            ->join('sales as s', 's.id', '=', 'sale_items.sale_id')
            ->whereColumn('batches.shop_id', 's.shop_id')
            ->where('s.shop_id', $shopId)
            ->selectRaw('sale_items.sale_id as sale_id')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
            ->groupBy('sale_items.sale_id');

        $q = (clone $salesQ)
            ->leftJoinSub($costSub, 'c', function ($join) {
                $join->on('c.sale_id', '=', 'sales.id');
            })
            ->select('sales.*')
            ->selectRaw('COALESCE(c.cost_total,0) as cost_total');

        $sales = $q->paginate(25)->withQueryString();

        // Totals (strict safe)
        $totalsBase = (clone $salesQ)->reorder();

        $totals = (object)[
            'sales_count'   => (int)(clone $totalsBase)->count(),
            'revenue_total' => (float)(clone $totalsBase)->sum('grand_total'),
            'cost_total'    => 0.0,
        ];

        // Cost total across filtered sales
        $costTotalQ = DB::table('sale_items')
            ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereColumn('batches.shop_id', 'sales.shop_id')
            ->where('sales.shop_id', $shopId);

        if ($request->filled('from')) {
            $costTotalQ->whereDate('sales.created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $costTotalQ->whereDate('sales.created_at', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $costTotalQ->where('sales.status', $request->status);
        }
        if ($request->filled('payment_method')) {
            $costTotalQ->where('sales.payment_method', $request->payment_method);
        }
        if ($request->filled('bank_id')) {
            $costTotalQ->where('sales.bank_id', (int)$request->bank_id);
        }

        $totals->cost_total = (float)$costTotalQ
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
            ->value('cost_total');

        $profit = (float)$totals->revenue_total - (float)$totals->cost_total;

        return view('panels.main.reports.sales', compact('sales', 'totals', 'profit', 'banks'));
    }

    public function returns(Request $request)
    {
        $this->assertMain();

        $shopId = (int)auth()->user()->shop_id;

        $qBase = SaleReturn::query()
            ->with(['shop', 'user', 'sale'])
            ->where('sale_returns.shop_id', $shopId)
            ->orderByDesc('sale_returns.id');

        if ($request->filled('from')) {
            $qBase->whereDate('sale_returns.created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $qBase->whereDate('sale_returns.created_at', '<=', $request->to);
        }
        if ($request->filled('method')) {
            $qBase->where('sale_returns.method', $request->method);
        }

        // STRICT-safe return cost subquery per return id
        $returnCostSub = DB::table('sale_return_items')
            ->join('batches', 'batches.id', '=', 'sale_return_items.batch_id')
            ->join('sale_returns as r', 'r.id', '=', 'sale_return_items.sale_return_id')
            ->whereColumn('batches.shop_id', 'r.shop_id')
            ->where('r.shop_id', $shopId)
            ->selectRaw('sale_return_items.sale_return_id as rid')
            ->selectRaw('COALESCE(SUM(sale_return_items.quantity * COALESCE(batches.cost_price,0)),0) as return_cost_total')
            ->groupBy('sale_return_items.sale_return_id');

        $q = (clone $qBase)
            ->leftJoinSub($returnCostSub, 'rc', function ($join) {
                $join->on('rc.rid', '=', 'sale_returns.id');
            })
            ->select('sale_returns.*')
            ->selectRaw('COALESCE(rc.return_cost_total,0) as return_cost_total');

        $returns = $q->paginate(25)->withQueryString();

        // Totals
        $totalsBase = (clone $qBase)->reorder();

        $totals = (object)[
            'return_count'      => (int)(clone $totalsBase)->count(),
            'refund_total'      => (float)(clone $totalsBase)->sum('refund_amount'),
            'return_cost_total' => 0.0,
        ];

        $returnCostTotalQ = DB::table('sale_return_items')
            ->join('batches', 'batches.id', '=', 'sale_return_items.batch_id')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->whereColumn('batches.shop_id', 'sale_returns.shop_id')
            ->where('sale_returns.shop_id', $shopId);

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

        return view('panels.main.reports.returns', compact('returns', 'totals'));
    }
}
