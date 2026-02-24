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

    $shopId = (int) auth()->user()->shop_id;

    // For filter dropdown
    $banks = Bank::query()
        ->where('shop_id', $shopId)
        ->orderBy('name')
        ->get();

    // Base sales query (list)
    $salesBase = Sale::query()
        ->where('sales.shop_id', $shopId)
        ->with(['shop','user','items'])
        ->orderByDesc('sales.id');

    if ($request->filled('from')) {
        $salesBase->whereDate('sales.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $salesBase->whereDate('sales.created_at', '<=', $request->to);
    }

    // default: exclude fully returned unless status filter applied
    if ($request->filled('status')) {
        $salesBase->where('sales.status', $request->status);
    } else {
        $salesBase->whereIn('sales.status', ['completed', 'partial_return']);
    }

    // sale_type filter
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

    // payment filters (sale must have at least one matching payment)
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
     * cost per sale
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
     * qty per sale
     */
    $qtySub = DB::table('sale_items')
        ->join('sales as s2', 's2.id', '=', 'sale_items.sale_id')
        ->where('s2.shop_id', $shopId)
        ->selectRaw('sale_items.sale_id as sale_id')
        ->selectRaw('COALESCE(SUM(sale_items.quantity),0) as qty_total')
        ->groupBy('sale_items.sale_id');

    // Apply SAME filters to costSub via s
    if ($request->filled('from')) $costSub->whereDate('s.created_at', '>=', $request->from);
    if ($request->filled('to'))   $costSub->whereDate('s.created_at', '<=', $request->to);

    if ($request->filled('status')) $costSub->where('s.status', $request->status);
    else $costSub->whereIn('s.status', ['completed', 'partial_return']);

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

    // Payment filters must be applied to subqueries too (otherwise mismatch)
    if ($request->filled('payment_method')) {
        $pm = $request->payment_method;
        $costSub->whereExists(function ($q) use ($pm) {
            $q->selectRaw('1')
              ->from('payments')
              ->whereColumn('payments.sale_id', 's.id')
              ->where('payments.method', $pm);
        });
    }
    if ($request->filled('bank_id')) {
        $bankId = (int)$request->bank_id;
        $costSub->whereExists(function ($q) use ($bankId) {
            $q->selectRaw('1')
              ->from('payments')
              ->whereColumn('payments.sale_id', 's.id')
              ->where('payments.bank_id', $bankId);
        });
    }

    // Apply SAME filters to qtySub via s2
    if ($request->filled('from')) $qtySub->whereDate('s2.created_at', '>=', $request->from);
    if ($request->filled('to'))   $qtySub->whereDate('s2.created_at', '<=', $request->to);

    if ($request->filled('status')) $qtySub->where('s2.status', $request->status);
    else $qtySub->whereIn('s2.status', ['completed', 'partial_return']);

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

    if ($request->filled('payment_method')) {
        $pm = $request->payment_method;
        $qtySub->whereExists(function ($q) use ($pm) {
            $q->selectRaw('1')
              ->from('payments')
              ->whereColumn('payments.sale_id', 's2.id')
              ->where('payments.method', $pm);
        });
    }
    if ($request->filled('bank_id')) {
        $bankId = (int)$request->bank_id;
        $qtySub->whereExists(function ($q) use ($bankId) {
            $q->selectRaw('1')
              ->from('payments')
              ->whereColumn('payments.sale_id', 's2.id')
              ->where('payments.bank_id', $bankId);
        });
    }

    /**
     * payment info subquery (latest payment)
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

    // Final list query
    $q = (clone $salesBase)
        ->leftJoinSub($costSub, 'c', fn($join) => $join->on('c.sale_id', '=', 'sales.id'))
        ->leftJoinSub($qtySub, 'qt', fn($join) => $join->on('qt.sale_id', '=', 'sales.id'))
        ->leftJoinSub($paymentInfoSub, 'pi', fn($join) => $join->on('pi.sale_id', '=', 'sales.id'))
        ->select('sales.*')
        ->selectRaw('COALESCE(c.cost_total,0) as cost_total')
        ->selectRaw('COALESCE(qt.qty_total,0) as qty_total')
        ->selectRaw('pi.payment_method as payment_method')
        ->selectRaw('pi.bank_name as bank_name');

    $sales = $q->paginate(25)->withQueryString();

    // Totals
    $totalsBase = (clone $salesBase)->reorder();

    $totals = (object)[
        'sales_count'   => (int)(clone $totalsBase)->count(),
        'revenue_total' => (float)(clone $totalsBase)->sum('grand_total'),
        'cost_total'    => 0.0,
        'qty_total'     => 0.0,
    ];

    // Cost total across filtered sales (must include payment filters too)
    $costTotalQ = DB::table('sale_items')
        ->join('batches', 'batches.id', '=', 'sale_items.batch_id')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->whereColumn('batches.shop_id', 'sales.shop_id')
        ->where('sales.shop_id', $shopId);

    // Qty total across filtered sales
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
            $q->selectRaw('1')->from('payments')
              ->whereColumn('payments.sale_id', 'sales.id')
              ->where('payments.method', $pm);
        });

        $qtyTotalQ->whereExists(function ($q) use ($pm) {
            $q->selectRaw('1')->from('payments')
              ->whereColumn('payments.sale_id', 'sales.id')
              ->where('payments.method', $pm);
        });
    }

    if ($request->filled('bank_id')) {
        $bankId = (int)$request->bank_id;

        $costTotalQ->whereExists(function ($q) use ($bankId) {
            $q->selectRaw('1')->from('payments')
              ->whereColumn('payments.sale_id', 'sales.id')
              ->where('payments.bank_id', $bankId);
        });

        $qtyTotalQ->whereExists(function ($q) use ($bankId) {
            $q->selectRaw('1')->from('payments')
              ->whereColumn('payments.sale_id', 'sales.id')
              ->where('payments.bank_id', $bankId);
        });
    }

    $totals->cost_total = (float)$costTotalQ
        ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(batches.cost_price,0)),0) as cost_total')
        ->value('cost_total');

    $totals->qty_total = (float)$qtyTotalQ
        ->selectRaw('COALESCE(SUM(sale_items.quantity),0) as qty_total')
        ->value('qty_total');

    $profit = (float)$totals->revenue_total - (float)$totals->cost_total;

    /**
     * ✅ NEW: Perfume-wise sold qty summary (same filters)
     * We group by item_name (you store perfume name in SaleItem::item_name)
     */
    $perfumeQtyQ = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->where('sales.shop_id', $shopId);

    if ($request->filled('from')) $perfumeQtyQ->whereDate('sales.created_at', '>=', $request->from);
    if ($request->filled('to'))   $perfumeQtyQ->whereDate('sales.created_at', '<=', $request->to);

    if ($request->filled('status')) $perfumeQtyQ->where('sales.status', $request->status);
    else $perfumeQtyQ->whereIn('sales.status', ['completed', 'partial_return']);

    if ($request->filled('sale_type')) {
        $st = $request->sale_type;
        if ($st === 'customer') {
            $perfumeQtyQ->where(function ($qq) {
                $qq->whereNull('sales.sale_type')->orWhere('sales.sale_type', 'customer');
            });
        } else {
            $perfumeQtyQ->where('sales.sale_type', $st);
        }
    }

    if ($request->filled('payment_method')) {
        $pm = $request->payment_method;
        $perfumeQtyQ->whereExists(function ($q) use ($pm) {
            $q->selectRaw('1')->from('payments')
              ->whereColumn('payments.sale_id', 'sales.id')
              ->where('payments.method', $pm);
        });
    }

    if ($request->filled('bank_id')) {
        $bankId = (int)$request->bank_id;
        $perfumeQtyQ->whereExists(function ($q) use ($bankId) {
            $q->selectRaw('1')->from('payments')
              ->whereColumn('payments.sale_id', 'sales.id')
              ->where('payments.bank_id', $bankId);
        });
    }

    $perfumeSummary = $perfumeQtyQ
        ->selectRaw('sale_items.item_name as name')
        ->selectRaw('COALESCE(SUM(sale_items.quantity),0) as qty')
        ->groupBy('sale_items.item_name')
        ->orderByDesc('qty')
        ->limit(50)
        ->get();

    return view('panels.main.reports.sales', compact('sales', 'totals', 'profit', 'banks', 'perfumeSummary'));
}

    // public function returns(Request $request)
    // {
    //     $this->assertMain();

    //     $shopId = (int)auth()->user()->shop_id;

    //     $qBase = SaleReturn::query()
    //         ->with(['shop', 'user', 'sale'])
    //         ->where('sale_returns.shop_id', $shopId)
    //         ->orderByDesc('sale_returns.id');

    //     if ($request->filled('from')) {
    //         $qBase->whereDate('sale_returns.created_at', '>=', $request->from);
    //     }
    //     if ($request->filled('to')) {
    //         $qBase->whereDate('sale_returns.created_at', '<=', $request->to);
    //     }
    //     if ($request->filled('method')) {
    //         $qBase->where('sale_returns.method', $request->method);
    //     }

    //     // STRICT-safe return cost subquery per return id
    //     $returnCostSub = DB::table('sale_return_items')
    //         ->join('batches', 'batches.id', '=', 'sale_return_items.batch_id')
    //         ->join('sale_returns as r', 'r.id', '=', 'sale_return_items.sale_return_id')
    //         ->whereColumn('batches.shop_id', 'r.shop_id')
    //         ->where('r.shop_id', $shopId)
    //         ->selectRaw('sale_return_items.sale_return_id as rid')
    //         ->selectRaw('COALESCE(SUM(sale_return_items.quantity * COALESCE(batches.cost_price,0)),0) as return_cost_total')
    //         ->groupBy('sale_return_items.sale_return_id');

    //     $q = (clone $qBase)
    //         ->leftJoinSub($returnCostSub, 'rc', function ($join) {
    //             $join->on('rc.rid', '=', 'sale_returns.id');
    //         })
    //         ->select('sale_returns.*')
    //         ->selectRaw('COALESCE(rc.return_cost_total,0) as return_cost_total');

    //     $returns = $q->paginate(25)->withQueryString();

    //     // Totals
    //     $totalsBase = (clone $qBase)->reorder();

    //     $totals = (object)[
    //         'return_count'      => (int)(clone $totalsBase)->count(),
    //         'refund_total'      => (float)(clone $totalsBase)->sum('refund_amount'),
    //         'return_cost_total' => 0.0,
    //     ];

    //     $returnCostTotalQ = DB::table('sale_return_items')
    //         ->join('batches', 'batches.id', '=', 'sale_return_items.batch_id')
    //         ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
    //         ->whereColumn('batches.shop_id', 'sale_returns.shop_id')
    //         ->where('sale_returns.shop_id', $shopId);

    //     if ($request->filled('from')) {
    //         $returnCostTotalQ->whereDate('sale_returns.created_at', '>=', $request->from);
    //     }
    //     if ($request->filled('to')) {
    //         $returnCostTotalQ->whereDate('sale_returns.created_at', '<=', $request->to);
    //     }
    //     if ($request->filled('method')) {
    //         $returnCostTotalQ->where('sale_returns.method', $request->method);
    //     }

    //     $totals->return_cost_total = (float)$returnCostTotalQ
    //         ->selectRaw('COALESCE(SUM(sale_return_items.quantity * COALESCE(batches.cost_price,0)),0) as return_cost_total')
    //         ->value('return_cost_total');

    //     return view('panels.main.reports.returns', compact('returns', 'totals'));
    // }

    public function returns(Request $request)
{
    $this->assertMain();

    $shopId = (int)auth()->user()->shop_id;

    $qBase = SaleReturn::query()
        ->with([
            'shop',
            'user',
            'sale',
            // ✅ NEW: load returned items + their sale item + batch (for barcode/name fallback)
            'items.saleItem',
            'items.batch.perfume',
        ])
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
