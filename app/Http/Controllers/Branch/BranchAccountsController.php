<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Shop;
use Illuminate\Http\Request;

class BranchAccountsController extends Controller
{
    private function assertBranch(): void
    {
        abort_if(!auth()->check(), 403);
        abort_if((auth()->user()->role ?? null) !== 'branch_shop', 403);
    }

    private function branchShopOrFail(): Shop
    {
        $this->assertBranch();

        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop || $shop->type !== 'branch', 403);

        return $shop;
    }

    public function index(Request $request)
    {
        $branch = $this->branchShopOrFail();

        $accounts = Account::query()
            ->where('shop_id', $branch->id)
            ->orderByDesc('id')
            ->get();

        return view('panels.branch.accounts.index', compact('branch', 'accounts'));
    }

    public function show(Account $account, Request $request)
    {
        $branch = $this->branchShopOrFail();

        // ✅ branch can only view own accounts
        abort_if((int)$account->shop_id !== (int)$branch->id, 403);

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

        return view('panels.branch.accounts.show', compact('branch', 'account', 'entries', 'totals', 'balance'));
    }

    public function storeEntry(Account $account, Request $request)
    {
        $branch = $this->branchShopOrFail();

        // ✅ branch can only add to own accounts
        abort_if((int)$account->shop_id !== (int)$branch->id, 403);

        $data = $request->validate([
            'entry_date'   => ['required', 'date'],
            'debit'        => ['nullable', 'numeric', 'min:0'],
            'credit'       => ['nullable', 'numeric', 'min:0'],
            'description'  => ['nullable', 'string'],
        ]);

        $debit  = (float)($data['debit'] ?? 0);
        $credit = (float)($data['credit'] ?? 0);

        abort_if($debit <= 0 && $credit <= 0, 422, 'Enter debit or credit amount.');
        abort_if($debit > 0 && $credit > 0, 422, 'Only one of debit/credit allowed per entry.');

        AccountEntry::create([
            'account_id'  => $account->id,
            'shop_id'     => (int)$branch->id,
            'user_id'     => auth()->id(),
            'entry_date'  => $data['entry_date'],
            'debit'       => round($debit, 2),
            'credit'      => round($credit, 2),
            'description' => $data['description'] ?? null,
            'ref_type'    => null,
            'ref_id'      => null,
        ]);

        return back()->with('success', 'Entry added successfully.');
    }
}