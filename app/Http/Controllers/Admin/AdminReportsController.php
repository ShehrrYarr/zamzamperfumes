<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shop;
use Illuminate\Http\Request;

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
            $q->where(function($qq) use ($s){
                $qq->where('barcode', 'like', "%{$s}%")
                   ->orWhere('batch_code', 'like', "%{$s}%"); // if you have
            });
        }

        // Totals
        $totals = (clone $q)
            ->selectRaw('COUNT(*) as batch_count')
            ->selectRaw('COALESCE(SUM(quantity),0) as total_qty')
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(cost_price,0)),0) as total_stock_cost')
            ->first();

        $batches = $q->paginate(25)->withQueryString();

        return view('admin.reports.batches', compact('batches','totals','shops'));
    }

    public function sales(Request $request)
    {
        $this->assertAdmin();

        $shops = Shop::orderBy('type')->orderBy('name')->get();

        // Sales with cost computed from sale_items × batches.cost_price
        $q = Sale::query()
            ->select('sales.*')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
            ->whereColumn('batches.shop_id', 'sales.shop_id') // safety
            ->with(['shop', 'user'])
            ->groupBy('sales.id')
            ->orderByDesc('sales.id');

        if ($request->filled('shop_id')) {
            $q->where('sales.shop_id', (int)$request->shop_id);
        }
        if ($request->filled('from')) {
            $q->whereDate('sales.created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('sales.created_at', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $q->where('sales.status', $request->status);
        }

        // Totals (use subquery to safely SUM the grouped cost_total)
        $totals = (clone $q)->getQuery()->cloneWithout(['orders','limit','offset'])->cloneWithoutBindings(['order'])
            ->selectRaw('COUNT(DISTINCT sales.id) as sales_count')
            ->selectRaw('COALESCE(SUM(DISTINCT sales.grand_total),0) as revenue_total') // distinct to avoid join duplication
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
            ->first();

        $revenue = (float)($totals->revenue_total ?? 0);
        $cost    = (float)($totals->cost_total ?? 0);
        $profit  = $revenue - $cost;

        $sales = $q->paginate(25)->withQueryString();

        return view('admin.reports.sales', compact('sales','totals','profit','shops'));
    }

    public function returns(Request $request)
    {
        $this->assertAdmin();

        $shops = Shop::orderBy('type')->orderBy('name')->get();

        // Returns with cost computed from sale_return_items × batches.cost_price
        $q = SaleReturn::query()
            ->select('sale_returns.*')
            ->selectRaw('COALESCE(SUM(sale_return_items.quantity * COALESCE(batches.cost_price,0)),0) as return_cost_total')
            ->join('sale_return_items', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
            ->join('batches', 'batches.id', '=', 'sale_return_items.batch_id')
            ->whereColumn('batches.shop_id', 'sale_returns.shop_id')
            ->with(['shop','user','sale'])
            ->groupBy('sale_returns.id')
            ->orderByDesc('sale_returns.id');

        if ($request->filled('shop_id')) {
            $q->where('sale_returns.shop_id', (int)$request->shop_id);
        }
        if ($request->filled('from')) {
            $q->whereDate('sale_returns.created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('sale_returns.created_at', '<=', $request->to);
        }
        if ($request->filled('method')) {
            $q->where('sale_returns.method', $request->method);
        }

        $totals = (clone $q)->getQuery()->cloneWithout(['orders','limit','offset'])->cloneWithoutBindings(['order'])
            ->selectRaw('COUNT(DISTINCT sale_returns.id) as return_count')
            ->selectRaw('COALESCE(SUM(DISTINCT sale_returns.refund_amount),0) as refund_total') // distinct to avoid join duplication
            ->selectRaw('COALESCE(SUM(sale_return_items.quantity * COALESCE(batches.cost_price,0)),0) as return_cost_total')
            ->first();

        $returns = $q->paginate(25)->withQueryString();

        return view('admin.reports.returns', compact('returns','totals','shops'));
    }
}
