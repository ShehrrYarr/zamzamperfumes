<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Shop;
use Illuminate\Http\Request;

class AdminAccountsController extends Controller
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

        $q = Account::query()->with('shop')->orderByDesc('id');

        if ($request->filled('shop_id')) {
            $q->where('shop_id', (int)$request->shop_id);
        }
        if ($request->filled('active')) {
            $q->where('is_active', $request->active === '1');
        }

        $accounts = $q->paginate(25)->withQueryString();

        return view('admin.accounts.index', compact('accounts','shops'));
    }

    public function create()
    {
        $this->assertAdmin();
        $shops = Shop::orderBy('type')->orderBy('name')->get();
        return view('admin.accounts.create', compact('shops'));
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'shop_id' => ['required','exists:shops,id'],
            'name' => ['required','string','max:255'],
            'code' => ['nullable','string','max:50'],
            'is_active' => ['nullable','boolean'],
        ]);

        Account::create([
            'shop_id' => (int)$data['shop_id'],
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.accounts.index')->with('success','Account created.');
    }

    public function show(Account $account, Request $request)
    {
        $this->assertAdmin();

        $account->load('shop');

        $entriesQ = AccountEntry::query()
            ->with('user')
            ->where('account_id', $account->id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        if ($request->filled('from')) {
            $entriesQ->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $entriesQ->whereDate('entry_date', '<=', $request->to);
        }

        $entries = $entriesQ->paginate(30)->withQueryString();

        $totals = (object)[
            'credit' => (float)(clone $entriesQ)->reorder()->sum('credit'),
            'debit'  => (float)(clone $entriesQ)->reorder()->sum('debit'),
        ];
        $balance = round($totals->credit - $totals->debit, 2);

        return view('admin.accounts.show', compact('account','entries','totals','balance'));
    }

    public function addEntry(Account $account, Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'entry_date' => ['required','date'],
            'type' => ['required','in:debit,credit'],
            'amount' => ['required','numeric','min:0.01'],
            'description' => ['nullable','string'],
        ]);

        $amount = round((float)$data['amount'], 2);

        AccountEntry::create([
            'account_id' => $account->id,
            'shop_id' => $account->shop_id,
            'user_id' => auth()->id(),
            'entry_date' => $data['entry_date'],
            'debit' => $data['type'] === 'debit' ? $amount : 0,
            'credit' => $data['type'] === 'credit' ? $amount : 0,
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('success','Entry added.');
    }
}