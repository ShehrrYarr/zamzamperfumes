<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Shop;
use Illuminate\Http\Request;

class AdminExpenseController extends Controller
{
    private function assertAdmin(): void
    {
        abort_if(!auth()->check(), 403);
        abort_if(auth()->user()->role !== 'admin', 403);
    }

    public function index(Request $request)
    {
        $this->assertAdmin();

        $shops = Shop::orderBy('type')->orderBy('name')->get();

        $shopId = $request->query('shop_id');
        $from   = $request->query('from');
        $to     = $request->query('to');
        $q      = trim((string)$request->query('q'));

        $exp = Expense::query()
            ->with(['shop','user'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        if ($shopId) $exp->where('shop_id', (int)$shopId);
        if ($from)   $exp->whereDate('expense_date', '>=', $from);
        if ($to)     $exp->whereDate('expense_date', '<=', $to);
        if ($q) {
            $exp->where(function($qq) use ($q){
                $qq->where('title','like',"%{$q}%")
                   ->orWhere('category','like',"%{$q}%")
                   ->orWhere('notes','like',"%{$q}%");
            });
        }

        $total = (clone $exp)->sum('amount');
        $items = $exp->paginate(30)->withQueryString();

        return view('admin.expenses.index', compact('items','total','shops','shopId','from','to','q'));
    }
}
