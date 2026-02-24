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

    private function applyCustomerSalesFilter($q): void
    {
        // customer sales = sale_type NULL or 'customer'
        $q->where(function ($qq) {
            $qq->whereNull('sale_type')
               ->orWhere('sale_type', 'customer');
        });
    }

    private function expenseQuery(int $shopId, string $date)
    {
        // If you have Expense model, replace this with Expense::query()...
        // Using DB::table keeps it flexible even if model not created.
        return DB::table('expenses')
            ->where('shop_id', $shopId)
            ->whereDate('created_at', $date);
    }

    public function index(Request $request)
    {
        $mainShop = $this->mainShopOrFail();
        $shopId = (int) $mainShop->id;

        $date = $request->query('date', now()->toDateString());

        /* -------------------
           1) Batches added today
        ------------------- */
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

        /* -------------------
           2) Sales created today (customer only)
        ------------------- */
        $salesQ = Sale::query()
            ->with(['user'])
            ->where('shop_id', $shopId)
            ->whereDate('created_at', $date);

        $this->applyCustomerSalesFilter($salesQ);

        // show sales statuses (optional)
        $salesQ->whereIn('status', ['completed', 'partial_return', 'returned']);

        $sales = (clone $salesQ)->orderByDesc('id')->get();

        $salesTotals = (clone $salesQ)
            ->reorder()
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(grand_total),0) as gross_sales')
            ->first();

        /* -------------------
           3) Payments processed today (sales + refunds)
           Refunds must be negative payments.
        ------------------- */
        $paymentsQ = Payment::query()
            ->with(['sale'])
            ->where('shop_id', $shopId)
            ->whereDate('paid_at', $date)
            ->whereHas('sale', function ($q) {
                $this->applyCustomerSalesFilter($q);
            });

        $payments = (clone $paymentsQ)->orderByDesc('id')->get();

        $counterBeforeExpense = (float) (clone $paymentsQ)->where('method', 'counter')->sum('amount');
        $bankNet              = (float) (clone $paymentsQ)->where('method', 'bank')->sum('amount');

        $refundCounter = (float) (clone $paymentsQ)->where('method','counter')->where('amount','<',0)->sum('amount');
        $refundBank    = (float) (clone $paymentsQ)->where('method','bank')->where('amount','<',0)->sum('amount');

        $refundTotals = [
            'counter' => abs($refundCounter),
            'bank'    => abs($refundBank),
            'total'   => abs($refundCounter) + abs($refundBank),
        ];

        /* -------------------
           4) Expenses today (affect COUNTER only)
        ------------------- */
        $expensesQ = $this->expenseQuery($shopId, $date);

        $expenses = (clone $expensesQ)
            ->orderByDesc('id')
            ->get();

        $expensesTotal = (float) (clone $expensesQ)
            ->selectRaw('COALESCE(SUM(amount),0) as total')
            ->value('total');

        $counterAfterExpense = (float) $counterBeforeExpense - (float) $expensesTotal;

        $netTotals = [
            'counter_before_expense' => round($counterBeforeExpense, 2),
            'expenses'               => round($expensesTotal, 2),
            'counter_after_expense'  => round($counterAfterExpense, 2),
            'bank'                   => round($bankNet, 2),
            'total_after_expense'    => round($counterAfterExpense + $bankNet, 2),
        ];

        /* -------------------
           5) Top perfumes sold today
        ------------------- */
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
            'expenses',
            'expensesTotal',
            'netTotals',
            'topPerfumes'
        ));
    }

    public function pdf(Request $request)
    {
        $mainShop = $this->mainShopOrFail();
        $shopId = (int) $mainShop->id;

        $date = $request->query('date', now()->toDateString());

        // Batches
        $batchesQ = Batch::query()->with('perfume')->where('shop_id', $shopId)->whereDate('created_at', $date);
        $batches = (clone $batchesQ)->orderByDesc('id')->get();
        $batchTotals = (clone $batchesQ)->reorder()
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(quantity),0) as qty')
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(cost_price,0)),0) as cost')
            ->first();

        // Sales
        $salesQ = Sale::query()->with(['user'])->where('shop_id', $shopId)->whereDate('created_at', $date);
        $this->applyCustomerSalesFilter($salesQ);
        $salesQ->whereIn('status', ['completed', 'partial_return', 'returned']);
        $sales = (clone $salesQ)->orderByDesc('id')->get();
        $salesTotals = (clone $salesQ)->reorder()
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(grand_total),0) as gross_sales')
            ->first();

        // Payments
        $paymentsQ = Payment::query()
            ->with(['sale'])
            ->where('shop_id', $shopId)
            ->whereDate('paid_at', $date)
            ->whereHas('sale', function ($q) {
                $this->applyCustomerSalesFilter($q);
            });

        $payments = (clone $paymentsQ)->orderByDesc('id')->get();

        $counterBeforeExpense = (float) (clone $paymentsQ)->where('method', 'counter')->sum('amount');
        $bankNet              = (float) (clone $paymentsQ)->where('method', 'bank')->sum('amount');

        $refundCounter = (float) (clone $paymentsQ)->where('method','counter')->where('amount','<',0)->sum('amount');
        $refundBank    = (float) (clone $paymentsQ)->where('method','bank')->where('amount','<',0)->sum('amount');

        $refundTotals = [
            'counter' => abs($refundCounter),
            'bank'    => abs($refundBank),
            'total'   => abs($refundCounter) + abs($refundBank),
        ];

        // Expenses
        $expensesQ = $this->expenseQuery($shopId, $date);
        $expenses = (clone $expensesQ)->orderByDesc('id')->get();
        $expensesTotal = (float) (clone $expensesQ)->selectRaw('COALESCE(SUM(amount),0) as total')->value('total');

        $counterAfterExpense = (float) $counterBeforeExpense - (float) $expensesTotal;

        $netTotals = [
            'counter_before_expense' => round($counterBeforeExpense, 2),
            'expenses'               => round($expensesTotal, 2),
            'counter_after_expense'  => round($counterAfterExpense, 2),
            'bank'                   => round($bankNet, 2),
            'total_after_expense'    => round($counterAfterExpense + $bankNet, 2),
        ];

        // Top perfumes
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
            'expenses',
            'expensesTotal',
            'netTotals',
            'topPerfumes'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("daily-report-{$mainShop->name}-{$date}.pdf");
    }
}