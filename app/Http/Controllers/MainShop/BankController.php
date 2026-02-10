<?php

namespace App\Http\Controllers\MainShop;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Shop;
use Illuminate\Http\Request;

class BankController extends Controller
{
    private function mainShopOrFail(): Shop
    {
        $shop = Shop::find(auth()->user()->shop_id);
        abort_if(!$shop || $shop->type !== 'main', 403);
        return $shop;
    }

    public function index()
    {
        $shop = $this->mainShopOrFail();
        $banks = Bank::where('shop_id', $shop->id)->orderByDesc('id')->get();
        return view('panels.main.banks.index', compact('banks'));
    }

    public function create()
    {
        $this->mainShopOrFail();
        return view('panels.main.banks.create');
    }

    public function store(Request $request)
    {
        $shop = $this->mainShopOrFail();

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'account_title' => ['nullable','string','max:255'],
            'account_number' => ['nullable','string','max:255'],
            'iban' => ['nullable','string','max:255'],
            'is_active' => ['nullable'],
        ]);

        Bank::create([
            'shop_id' => $shop->id,
            'name' => $data['name'],
            'account_title' => $data['account_title'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'iban' => $data['iban'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('main.banks.index')->with('success','Bank created.');
    }

    public function edit(Bank $bank)
    {
        $shop = $this->mainShopOrFail();
        abort_if($bank->shop_id !== $shop->id, 403);
        return view('panels.main.banks.edit', compact('bank'));
    }

    public function update(Request $request, Bank $bank)
    {
        $shop = $this->mainShopOrFail();
        abort_if($bank->shop_id !== $shop->id, 403);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'account_title' => ['nullable','string','max:255'],
            'account_number' => ['nullable','string','max:255'],
            'iban' => ['nullable','string','max:255'],
            'is_active' => ['nullable'],
        ]);

        $bank->update([
            'name' => $data['name'],
            'account_title' => $data['account_title'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'iban' => $data['iban'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('main.banks.index')->with('success','Bank updated.');
    }
}
