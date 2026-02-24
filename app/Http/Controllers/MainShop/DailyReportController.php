<?php

namespace App\Http\Controllers\MainShop;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyReportController extends Controller
{
    private function mainShopOrFail(): Shop
    {
        abort_if(!auth()->check(), 403);

        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop || $shop->type !== 'main', 403);

        return $shop;
    }

    private function customerSalesFilter($q)
    {
        // ✅ customer sales are: sale_type NULL or 'customer'
        return $q->where(function ($qq) {
            $qq->whereNull('sale_type')->orWhere('sale_type', 'customer');
        });
    }

    public function index(Request $request)
    {
        $mainShop = $this->mainShopOrFail();
        $shopId = (int) $mainShop->id;

        $date = $request->query('date', now()->toDateString());

        // -------------------
        // 1) Batches added today
        // -------------------
        $batchesQ = Batch::query()
            ->with('perfume')
            ->where('shop_id', $shopId)
            ->whereDate('created_at', $date);

        $batches = (clone $batchesQ)->orderByDesc('id')->get();

        $batchTotals = (clone $batchesQ)
            ->reorder()
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(quantity),0) as qty')
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(cost_price,0)),0) as cost')
            ->first();

        // -------------------
        // 2) Sales created today (customer only)
        // -------------------
        $salesQ = Sale::query()
            ->with(['user'])
            ->where('shop_id', $shopId)
            ->whereDate('created_at', $date);

        $this->customerSalesFilter($salesQ);

        // statuses you want to show on report
        $salesQ->whereIn('status', ['completed', 'partial_return', 'returned']);

        $sales = $salesQ->orderByDesc('id')->get();

        $salesTotals = (clone $salesQ)
            ->reorder()
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(grand_total),0) as gross_sales')
            ->first();

        // -------------------
        // 3) Payments processed today (THIS is the money truth)
        // refunds are negative amounts
        // -------------------
        $paymentsQ = Payment::query()
            ->with(['sale'])
            ->where('shop_id', $shopId)
            ->whereDate('paid_at', $date)
            ->whereHas('sale', function ($q) {
                $this->customerSalesFilter($q);
            });

        $payments = (clone $paymentsQ)->orderByDesc('id')->get();

        // net totals by method (refunds reduce)
        $counterNet = (float) (clone $paymentsQ)->where('method', 'counter')->sum('amount');
        $bankNet    = (float) (clone $paymentsQ)->where('method', 'bank')->sum('amount');

        // refunds only (absolute)
        $refundCounter = (float) (clone $paymentsQ)->where('method','counter')->where('amount','<',0)->sum('amount');
        $refundBank    = (float) (clone $paymentsQ)->where('method','bank')->where('amount','<',0)->sum('amount');

        $refundTotals = [
            'counter' => abs($refundCounter),
            'bank'    => abs($refundBank),
            'total'   => abs($refundCounter) + abs($refundBank),
        ];

        $netTotals = [
            'counter' => round($counterNet, 2),
            'bank'    => round($bankNet, 2),
            'total'   => round($counterNet + $bankNet, 2),
        ];

        // -------------------
        // 4) Top perfumes sold today (customer sales, based on sale_items)
        // -------------------
        $topPerfumes = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.shop_id', $shopId)
            ->whereDate('sales.created_at', $date)
            ->whereIn('sales.status', ['completed', 'partial_return', 'returned'])
            ->where(function ($qq) {
                $qq->whereNull('sales.sale_type')->orWhere('sales.sale_type', 'customer');
            })
            ->selectRaw('sale_items.item_name as name, SUM(sale_items.quantity) as qty')
            ->groupBy('sale_items.item_name')
            ->orderByDesc('qty')
            ->limit(30)
            ->get();

        return view('panels.main.reports.daily', compact(
            'mainShop',
            'date',
            'batches',
            'batchTotals',
            'sales',
            'salesTotals',
            'payments',
            'refundTotals',
            'netTotals',
            'topPerfumes'
        ));
    }

    public function pdf(Request $request)
    {
        $mainShop = $this->mainShopOrFail();

        // reuse same logic by calling index data pattern (simple + safe approach):
        // (We’ll just duplicate “date” and run same queries quickly.)
        $shopId = (int) $mainShop->id;
        $date = $request->query('date', now()->toDateString());

        $batchesQ = Batch::query()->with('perfume')->where('shop_id', $shopId)->whereDate('created_at', $date);
        $batches = (clone $batchesQ)->orderByDesc('id')->get();
        $batchTotals = (clone $batchesQ)->reorder()
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(quantity),0) as qty')
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(cost_price,0)),0) as cost')
            ->first();

        $salesQ = Sale::query()->with(['user'])->where('shop_id', $shopId)->whereDate('created_at', $date);
        $this->customerSalesFilter($salesQ);
        $salesQ->whereIn('status', ['completed', 'partial_return', 'returned']);
        $sales = $salesQ->orderByDesc('id')->get();
        $salesTotals = (clone $salesQ)->reorder()
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(grand_total),0) as gross_sales')
            ->first();

        $paymentsQ = Payment::query()
            ->with(['sale'])
            ->where('shop_id', $shopId)
            ->whereDate('paid_at', $date)
            ->whereHas('sale', function ($q) {
                $this->customerSalesFilter($q);
            });

        $payments = (clone $paymentsQ)->orderByDesc('id')->get();
        $counterNet = (float) (clone $paymentsQ)->where('method', 'counter')->sum('amount');
        $bankNet    = (float) (clone $paymentsQ)->where('method', 'bank')->sum('amount');

        $refundCounter = (float) (clone $paymentsQ)->where('method','counter')->where('amount','<',0)->sum('amount');
        $refundBank    = (float) (clone $paymentsQ)->where('method','bank')->where('amount','<',0)->sum('amount');

        $refundTotals = [
            'counter' => abs($refundCounter),
            'bank'    => abs($refundBank),
            'total'   => abs($refundCounter) + abs($refundBank),
        ];

        $netTotals = [
            'counter' => round($counterNet, 2),
            'bank'    => round($bankNet, 2),
            'total'   => round($counterNet + $bankNet, 2),
        ];

        $topPerfumes = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.shop_id', $shopId)
            ->whereDate('sales.created_at', $date)
            ->whereIn('sales.status', ['completed', 'partial_return', 'returned'])
            ->where(function ($qq) {
                $qq->whereNull('sales.sale_type')->orWhere('sales.sale_type', 'customer');
            })
            ->selectRaw('sale_items.item_name as name, SUM(sale_items.quantity) as qty')
            ->groupBy('sale_items.item_name')
            ->orderByDesc('qty')
            ->limit(30)
            ->get();

        $pdf = Pdf::loadView('panels.main.reports.daily_pdf', compact(
            'mainShop',
            'date',
            'batches',
            'batchTotals',
            'sales',
            'salesTotals',
            'payments',
            'refundTotals',
            'netTotals',
            'topPerfumes'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("daily-report-{$mainShop->name}-{$date}.pdf");
    }
}