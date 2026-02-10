<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\BatchTransfer;
use App\Models\Shop;
use Illuminate\Http\Request;

class TransferHistoryController extends Controller
{
    private function branchShopOrFail(): Shop
    {
        $user = auth()->user();
        $shop = Shop::find($user->shop_id);
        abort_if(!$shop || $shop->type !== 'branch', 403);
        return $shop;
    }

    public function index(Request $request)
    {
        $branch = $this->branchShopOrFail();

        $status = $request->query('status', 'all');
        $from = $request->query('from');
        $to = $request->query('to');

        $q = BatchTransfer::with(['fromShop', 'items.batch.perfume'])
            ->where('to_shop_id', $branch->id);

        if ($status !== 'all') {
            $q->where('status', $status);
        }

        if ($from) {
            $q->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $q->whereDate('created_at', '<=', $to);
        }

        $transfers = $q->orderByDesc('id')->get();

        return view('panels.branch.transfers.index', compact('transfers', 'status', 'from', 'to', 'branch'));
    }
}
