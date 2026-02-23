<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Sale;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchReportsController extends Controller
{
    /**
     * Ensures current user belongs to a branch shop.
     */
    private function branchShopOrFail(): Shop
    {
        abort_if(!auth()->check(), 403, 'Unauthenticated.');

        $shopId = (int) auth()->user()->shop_id;
        $shop = Shop::find($shopId);

        abort_if(!$shop, 403, 'Shop not found for current user.');
        abort_if($shop->type !== 'branch', 403, 'Only branch users can access this page.');

        return $shop;
    }

    /**
     * Branch Sales Report
     */
    public function sales(Request $request)
    {
        $branch = $this->branchShopOrFail();
        $shopId = (int) $branch->id;

        // Banks for filter dropdown (branch banks only)
        $banks = Bank::query()
            ->where('shop_id', $shopId)
            ->orderBy('name')
            ->get();

        // Base sales query
        $salesBase = Sale::query()
            ->where('sales.shop_id', $shopId)
            ->with(['shop', 'user', 'items'])
            ->orderByDesc('sales.id');

        if ($request->filled('from')) {
            $salesBase->whereDate('sales.created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $salesBase->whereDate('sales.created_at', '<=', $request->to);
        }

        // ✅ Default behavior: exclude fully returned sales unless status filter applied
        if ($request->filled('status')) {
            $salesBase->where('sales.status', $request->status);
        } else {
            $salesBase->whereIn('sales.status', ['completed', 'partial_return']);
        }

        // ✅ Sale type filter (customer vs internal_transfer)
        if ($request->filled('sale_type')) {
            $st = $request->sale_type;

            if ($st === 'customer') {
                $salesBase->where(function ($qq) {
                    $qq->whereNull('sales.sale_type')
                       ->orWhere('sales.sale_type', 'customer');
                });
            } else {
                $salesBase->where('sales.sale_type', $st);
            }
        }

        /**
         * Payment filters (sales that have at least one payment matching)
         */
        if ($request->filled('payment_method')) {
            $pm = $request->payment_method; // counter|bank
            $salesBase->whereExists(function ($q) use ($pm) {
                $q->selectRaw('1')
                  ->from('payments')
                  ->whereColumn('payments.sale_id', 'sales.id')
                  ->where('payments.method', $pm);
            });
        }

        if ($request->filled('bank_id')) {
            $bankId = (int) $request->bank_id;
            $salesBase->whereExists(function ($q) use ($bankId) {
                $q->selectRaw('1')
                  ->from('payments')
                  ->whereColumn('payments.sale_id', 'sales.id')
                  ->where('payments.bank_id', $bankId);
            });
        }

        /**
         * ✅ Cost subquery (strict safe)
         */
        $costSub = DB::table('sale_items')
            ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
            ->join('sales as s', 's.id', '=', 'sale_items.sale_id')
            ->whereColumn('batches.shop_id', 's.shop_id')
            ->where('s.shop_id', $shopId)
            ->selectRaw('sale_items.sale_id as sale_id')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
            ->groupBy('sale_items.sale_id');

        /**
         * ✅ Quantity subquery (sum qty per sale)
         */
        $qtySub = DB::table('sale_items')
            ->join('sales as s2', 's2.id', '=', 'sale_items.sale_id')
            ->where('s2.shop_id', $shopId)
            ->selectRaw('sale_items.sale_id as sale_id')
            ->selectRaw('COALESCE(SUM(sale_items.quantity),0) as qty_total')
            ->groupBy('sale_items.sale_id');

        // Apply SAME filters to costSub through "s"
        if ($request->filled('from')) $costSub->whereDate('s.created_at', '>=', $request->from);
        if ($request->filled('to'))   $costSub->whereDate('s.created_at', '<=', $request->to);

        if ($request->filled('status')) {
            $costSub->where('s.status', $request->status);
        } else {
            $costSub->whereIn('s.status', ['completed', 'partial_return']);
        }

        if ($request->filled('sale_type')) {
            $st = $request->sale_type;
            if ($st === 'customer') {
                $costSub->where(function ($qq) {
                    $qq->whereNull('s.sale_type')->orWhere('s.sale_type', 'customer');
                });
            } else {
                $costSub->where('s.sale_type', $st);
            }
        }

        // Apply SAME filters to qtySub through "s2"
        if ($request->filled('from')) $qtySub->whereDate('s2.created_at', '>=', $request->from);
        if ($request->filled('to'))   $qtySub->whereDate('s2.created_at', '<=', $request->to);

        if ($request->filled('status')) {
            $qtySub->where('s2.status', $request->status);
        } else {
            $qtySub->whereIn('s2.status', ['completed', 'partial_return']);
        }

        if ($request->filled('sale_type')) {
            $st = $request->sale_type;
            if ($st === 'customer') {
                $qtySub->where(function ($qq) {
                    $qq->whereNull('s2.sale_type')->orWhere('s2.sale_type', 'customer');
                });
            } else {
                $qtySub->where('s2.sale_type', $st);
            }
        }

        /**
         * ✅ Payment info (most recent payment per sale)
         */
        $paymentSub = DB::table('payments as p')
            ->selectRaw('p.sale_id')
            ->selectRaw('MAX(p.id) as last_payment_id')
            ->groupBy('p.sale_id');

        $paymentInfoSub = DB::table('payments as p2')
            ->leftJoin('banks as b2', 'b2.id', '=', 'p2.bank_id')
            ->joinSub($paymentSub, 'lp', function ($join) {
                $join->on('lp.last_payment_id', '=', 'p2.id');
            })
            ->selectRaw('p2.sale_id')
            ->selectRaw('p2.method as payment_method')
            ->selectRaw('b2.name as bank_name');

        // Final query
        $q = (clone $salesBase)
            ->leftJoinSub($costSub, 'c', function ($join) {
                $join->on('c.sale_id', '=', 'sales.id');
            })
            ->leftJoinSub($qtySub, 'qt', function ($join) {
                $join->on('qt.sale_id', '=', 'sales.id');
            })
            ->leftJoinSub($paymentInfoSub, 'pi', function ($join) {
                $join->on('pi.sale_id', '=', 'sales.id');
            })
            ->select('sales.*')
            ->selectRaw('COALESCE(c.cost_total,0) as cost_total')
            ->selectRaw('COALESCE(qt.qty_total,0) as qty_total')
            ->selectRaw('pi.payment_method as payment_method')
            ->selectRaw('pi.bank_name as bank_name');

        $sales = $q->paginate(25)->withQueryString();

        /**
         * Totals
         */
        $totalsBase = (clone $salesBase)->reorder();

        $totals = (object)[
            'sales_count'   => (int) (clone $totalsBase)->count(),
            'revenue_total' => (float) (clone $totalsBase)->sum('grand_total'),
            'cost_total'    => 0.0,
            'qty_total'     => 0.0,
        ];

        $costTotalQ = DB::table('sale_items')
            ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereColumn('batches.shop_id', 'sales.shop_id')
            ->where('sales.shop_id', $shopId);

        $qtyTotalQ = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.shop_id', $shopId);

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
                    $qq->whereNull('sales.sale_type')->orWhere('sales.sale_type', 'customer');
                });
                $qtyTotalQ->where(function ($qq) {
                    $qq->whereNull('sales.sale_type')->orWhere('sales.sale_type', 'customer');
                });
            } else {
                $costTotalQ->where('sales.sale_type', $st);
                $qtyTotalQ->where('sales.sale_type', $st);
            }
        }

        if ($request->filled('payment_method')) {
            $pm = $request->payment_method;
            $costTotalQ->whereExists(function ($q) use ($pm) {
                $q->selectRaw('1')
                  ->from('payments')
                  ->whereColumn('payments.sale_id', 'sales.id')
                  ->where('payments.method', $pm);
            });
            $qtyTotalQ->whereExists(function ($q) use ($pm) {
                $q->selectRaw('1')
                  ->from('payments')
                  ->whereColumn('payments.sale_id', 'sales.id')
                  ->where('payments.method', $pm);
            });
        }

        if ($request->filled('bank_id')) {
            $bankId = (int) $request->bank_id;
            $costTotalQ->whereExists(function ($q) use ($bankId) {
                $q->selectRaw('1')
                  ->from('payments')
                  ->whereColumn('payments.sale_id', 'sales.id')
                  ->where('payments.bank_id', $bankId);
            });
            $qtyTotalQ->whereExists(function ($q) use ($bankId) {
                $q->selectRaw('1')
                  ->from('payments')
                  ->whereColumn('payments.sale_id', 'sales.id')
                  ->where('payments.bank_id', $bankId);
            });
        }

        $totals->cost_total = (float) $costTotalQ
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
            ->value('cost_total');

        $totals->qty_total = (float) $qtyTotalQ
            ->selectRaw('COALESCE(SUM(sale_items.quantity),0) as qty_total')
            ->value('qty_total');

        $profit = (float) $totals->revenue_total - (float) $totals->cost_total;

        return view('panels.branch.reports.sales', compact('sales', 'totals', 'profit', 'banks', 'branch'));
    }
}