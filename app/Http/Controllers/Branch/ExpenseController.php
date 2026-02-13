<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Shop;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    private function branchShopOrFail(): Shop
    {
        abort_if(!auth()->check(), 403);
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop || $shop->type !== 'branch', 403);
        return $shop;
    }

    public function index(Request $request)
    {
        $shop = $this->branchShopOrFail();

        $from = $request->query('from');
        $to   = $request->query('to');
        $q    = trim((string)$request->query('q'));

        $exp = Expense::query()
            ->where('shop_id', $shop->id)
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        if ($from) $exp->whereDate('expense_date', '>=', $from);
        if ($to)   $exp->whereDate('expense_date', '<=', $to);
        if ($q) {
            $exp->where(function($qq) use ($q){
                $qq->where('title','like',"%{$q}%")
                   ->orWhere('category','like',"%{$q}%")
                   ->orWhere('notes','like',"%{$q}%");
            });
        }

        $total = (clone $exp)->sum('amount');
        $items = $exp->paginate(25)->withQueryString();

        return view('panels.branch.expenses.index', compact('items','total','from','to','q'));
    }

    public function create()
    {
        $this->branchShopOrFail();
        return view('panels.branch.expenses.create');
    }

    public function store(Request $request)
    {
        $shop = $this->branchShopOrFail();

        $data = $request->validate([
            'expense_date' => ['required','date'],
            'category'     => ['nullable','string','max:80'],
            'title'        => ['required','string','max:140'],
            'notes'        => ['nullable','string'],
            'amount'       => ['required','numeric','min:0.01'],
        ]);

        Expense::create([
            'shop_id'       => $shop->id,
            'user_id'       => auth()->id(),
            'expense_date'  => $data['expense_date'],
            'category'      => $data['category'] ?? null,
            'title'         => $data['title'],
            'notes'         => $data['notes'] ?? null,
            'amount'        => round((float)$data['amount'], 2),
        ]);

        return redirect()->route('branch.expenses.index')->with('success','Expense added.');
    }
}
