<?php

namespace App\Http\Controllers\MainShop;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainAccountsController extends Controller
{
    private function assertMain(): void
    {
        abort_if(auth()->user()->role !== 'main_shop', 403);
    }

    /**
     * List all accounts (ALL shops) + summary (optional) + filters
     */
    public function index(Request $request)
    {
        $this->assertMain();

        $shops = Shop::orderBy('type')->orderBy('name')->get();

        $q = Account::query()
            ->with(['shop'])
            ->orderByDesc('id');

        // filters
        if ($request->filled('shop_id')) {
            $q->where('shop_id', (int)$request->shop_id);
        }

        if ($request->filled('q')) {
            $term = trim((string)$request->q);
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhereHas('shop', function ($s) use ($term) {
                      $s->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                  });
            });
        }

        $accounts = $q->paginate(25)->withQueryString();

        // OPTIONAL totals for visible filters (across entries)
        // If you already do totals in admin side, keep it here too.
        $totalsQ = AccountEntry::query();

        if ($request->filled('shop_id')) {
            $totalsQ->where('shop_id', (int)$request->shop_id);
        }
        if ($request->filled('from')) {
            $totalsQ->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $totalsQ->whereDate('entry_date', '<=', $request->to);
        }

        $totals = (object)[
            'debit_total'  => (float)(clone $totalsQ)->sum('debit'),
            'credit_total' => (float)(clone $totalsQ)->sum('credit'),
        ];

        return view('panels.main.accounts.index', compact('accounts', 'shops', 'totals'));
    }

    /**
     * Show single account + entries (with filters)
     */
   public function show(Account $account, Request $request)
{
    $this->assertMain();

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

    // same pagination style (you can keep 25 or change to 30, your choice)
    $entries = $entriesQ->paginate(30)->withQueryString();

    // totals in the SAME way admin does it (using filtered query)
    $totals = (object)[
        'credit' => (float) (clone $entriesQ)->reorder()->sum('credit'),
        'debit'  => (float) (clone $entriesQ)->reorder()->sum('debit'),
    ];

    $balance = round($totals->credit - $totals->debit, 2);

    return view('panels.main.accounts.show', compact('account', 'entries', 'totals', 'balance'));
}

    /**
     * Create a debit/credit entry for ANY shop account (main shop is allowed)
     */
    public function storeEntry(Request $request, Account $account)
    {
        $this->assertMain();

        $data = $request->validate([
            'entry_date'   => ['required', 'date'],
            'type'         => ['required', 'in:debit,credit'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'ref_type'     => ['nullable', 'string', 'max:100'],
            'ref_id'       => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($account, $data) {
            $debit = 0.0;
            $credit = 0.0;

            if ($data['type'] === 'debit') {
                $debit = (float)$data['amount'];
            } else {
                $credit = (float)$data['amount'];
            }

            AccountEntry::create([
                'account_id'  => $account->id,
                'shop_id'     => $account->shop_id, // matches your schema
                'user_id'     => auth()->id(),
                'entry_date'  => $data['entry_date'],
                'debit'       => round($debit, 2),
                'credit'      => round($credit, 2),
                'description' => $data['description'] ?? null,
                'ref_type'    => $data['ref_type'] ?? null,
                'ref_id'      => $data['ref_id'] ?? null,
            ]);
        });

        return back()->with('success', 'Account entry added.');
    }

    /**
     * OPTIONAL: delete entry (if you want parity with admin)
     * If you already have it in admin, keep it here too.
     */
    public function deleteEntry(AccountEntry $entry)
    {
        $this->assertMain();

        // main shop can delete entries too (if you want)
        $entry->delete();

        return back()->with('success', 'Entry deleted.');
    }
}