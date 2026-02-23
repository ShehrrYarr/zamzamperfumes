<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Batch;
use App\Models\BatchTransfer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferClaimController extends Controller
{
    private function branchShopOrFail(): Shop
    {
        abort_if(!auth()->check(), 403);

        $user = auth()->user();
        $shop = Shop::find($user->shop_id);

        abort_if(!$shop || $shop->type !== 'branch', 403);
        return $shop;
    }

    public function showClaimForm()
    {
        $this->branchShopOrFail();
        return view('panels.branch.transfers.claim');
    }

    private function firstAccountOrFail(int $shopId): Account
    {
        $acc = Account::query()
            ->where('shop_id', $shopId)
            ->orderBy('id') // ✅ first account per shop
            ->first();

        abort_if(!$acc, 422, "No account found for shop #{$shopId}. Ask admin to create it.");
        return $acc;
    }

    public function claim(Request $request)
    {
        $receiverBranch = $this->branchShopOrFail();

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        try {
            DB::beginTransaction();

            /** @var BatchTransfer $transfer */
            $transfer = BatchTransfer::query()
                ->where('code', trim($data['code']))
                ->lockForUpdate()
                ->first();

            abort_if(!$transfer, 404, 'Invalid code.');
            abort_if($transfer->status !== 'pending', 422, 'This code is already used or cancelled.');
            abort_if((int)$transfer->to_shop_id !== (int)$receiverBranch->id, 403, 'This code is not for your branch.');

            // lock sender shop row too
            $senderShop = Shop::query()
                ->where('id', (int)$transfer->from_shop_id)
                ->lockForUpdate()
                ->first();

            abort_if(!$senderShop, 422, 'Sender shop not found.');

            $transfer->load(['items.batch.perfume']);
            abort_if($transfer->items->count() === 0, 422, 'Transfer has no items.');

            // Decide path based on sender type
            if ($senderShop->type === 'main') {
                // -----------------------------
                // MAIN -> BRANCH (your current logic)
                // -----------------------------
                $sale = $this->claimFromMainShop($transfer, $senderShop, $receiverBranch);

                // mark transfer claimed
                $transfer->status = 'claimed';
                $transfer->claimed_at = now();
                if (\Schema::hasColumn('batch_transfers', 'claimed_by')) {
                    $transfer->claimed_by = auth()->id();
                }
                $transfer->save();

                DB::commit();

                return back()->with(
                    'success',
                    'Transfer claimed + internal sale created. Code: ' . $transfer->code . ' | Sale #' . $sale->id
                );
            }

            if ($senderShop->type === 'branch') {
                // -----------------------------
                // BRANCH -> BRANCH (new logic)
                // -----------------------------
                $this->claimFromBranch($transfer, $senderShop, $receiverBranch);

                // mark transfer claimed
                $transfer->status = 'claimed';
                $transfer->claimed_at = now();
                if (\Schema::hasColumn('batch_transfers', 'claimed_by')) {
                    $transfer->claimed_by = auth()->id();
                }
                $transfer->save();

                DB::commit();

                return back()->with('success', 'Branch transfer claimed successfully. Code: ' . $transfer->code);
            }

            abort(422, 'Invalid sender shop type.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * MAIN -> BRANCH
     * - branch stock increased
     * - branch cost = sell * 0.85
     * - internal sale created in MAIN (15% discount, counter payment)
     * - account entries:
     *   - Branch: debit grandTotal
     *   - Main: credit grandTotal
     */
    private function claimFromMainShop(BatchTransfer $transfer, Shop $mainShop, Shop $branch): Sale
    {
        $subtotal = 0.0;

        foreach ($transfer->items as $item) {
            $mainBatch = $item->batch;
            abort_if(!$mainBatch, 422, 'Batch missing for one of the transfer items.');

            $qty = (int)$item->quantity;
            abort_if($qty < 1, 422, 'Invalid quantity in transfer item.');

            $sell = (float)($mainBatch->sell_price ?? 0);
            $subtotal += ($sell * $qty);

            // Branch batch by barcode
            $branchBatch = Batch::query()
                ->where('shop_id', $branch->id)
                ->where('barcode', $mainBatch->barcode)
                ->lockForUpdate()
                ->first();

            $branchCost = round($sell * 0.85, 2); // 15% discount from sell

            if (!$branchBatch) {
                $branchBatch = Batch::create([
                    'perfume_id' => $mainBatch->perfume_id,
                    'shop_id'    => $branch->id,
                    'barcode'    => $mainBatch->barcode,
                    'batch_no'   => $mainBatch->batch_no,
                    'quantity'   => 0,
                    'cost_price' => $branchCost,
                    'sell_price' => $sell,
                    'mfg_date'   => $mainBatch->mfg_date,
                    'exp_date'   => $mainBatch->exp_date,
                    'is_active'  => true,
                ]);
            } else {
                $branchBatch->sell_price = $sell;
                $branchBatch->cost_price = $branchCost;
                $branchBatch->save();
            }

            $branchBatch->increment('quantity', $qty);
        }

        // Internal sale in MAIN (15% discount)
        $discountAmount = round(min(($subtotal * 0.15), $subtotal), 2);
        $grandTotal = round(max($subtotal - $discountAmount, 0), 2);

        $sale = Sale::create([
            'shop_id'         => (int)$mainShop->id,
            'user_id'         => auth()->id(),
            'customer_name'   => 'Branch Transfer — ' . ($branch->name ?? ('Branch#' . $branch->id)),
            'customer_phone'  => null,

            'subtotal'        => round($subtotal, 2),
            'discount_type'   => 'percent',
            'discount_value'  => 15.0,
            'discount_amount' => $discountAmount,
            'grand_total'     => $grandTotal,

            'status'          => 'completed',

            // flags for return linkage logic
            'sale_type'       => 'internal_transfer',
            'related_shop_id' => (int)$branch->id,
            'transfer_id'     => (int)$transfer->id,
        ]);

        foreach ($transfer->items as $item) {
            $mainBatch = $item->batch;
            $qty = (int)$item->quantity;

            $sell = (float)($mainBatch->sell_price ?? 0);

            SaleItem::create([
                'sale_id'    => $sale->id,
                'batch_id'   => $mainBatch->id, // main batch id
                'barcode'    => $mainBatch->barcode,
                'item_name'  => $mainBatch->perfume?->name ?? ('Batch#' . $mainBatch->id),
                'unit_price' => round($sell, 2),
                'quantity'   => $qty,
                'original_quantity'   => $qty,
                'line_total' => round($sell * $qty, 2),
            ]);
        }

        Payment::create([
            'shop_id' => (int)$mainShop->id,
            'sale_id' => $sale->id,
            'method'  => 'counter',
            'bank_id' => null,
            'amount'  => $grandTotal,
            'paid_at' => now(),
        ]);

        // Accounts posting (first account per shop) — prevent duplicates
        $refType = 'main_transfer_claim';
        $refId = (int)$transfer->id;

        $already = AccountEntry::query()
            ->where('ref_type', $refType)
            ->where('ref_id', $refId)
            ->exists();

        if (!$already) {
            $branchAccount = $this->firstAccountOrFail((int)$branch->id);
            $mainAccount   = $this->firstAccountOrFail((int)$mainShop->id);

            $desc = "Main→Branch transfer claimed {$transfer->code} | Internal Sale #{$sale->id} | 15% discount";

            // Branch: DEBIT
            AccountEntry::create([
                'account_id'  => $branchAccount->id,
                'shop_id'     => (int)$branch->id,
                'user_id'     => auth()->id(),
                'entry_date'  => now()->toDateString(),
                'debit'       => $grandTotal,
                'credit'      => 0,
                'description' => $desc,
                'ref_type'    => $refType,
                'ref_id'      => $refId,
            ]);

            // Main: CREDIT
            AccountEntry::create([
                'account_id'  => $mainAccount->id,
                'shop_id'     => (int)$mainShop->id,
                'user_id'     => auth()->id(),
                'entry_date'  => now()->toDateString(),
                'debit'       => 0,
                'credit'      => $grandTotal,
                'description' => $desc,
                'ref_type'    => $refType,
                'ref_id'      => $refId,
            ]);
        }

        return $sale;
    }

 
    private function claimFromBranch(BatchTransfer $transfer, Shop $senderBranch, Shop $receiverBranch): void
    {
        // safety: branches only (no main)
        abort_if($senderBranch->type !== 'branch', 422, 'Sender must be a branch.');
        abort_if($receiverBranch->type !== 'branch', 422, 'Receiver must be a branch.');

        $costTotal = 0.0;

        foreach ($transfer->items as $item) {
            $qty = (int)$item->quantity;
            abort_if($qty < 1, 422, 'Invalid quantity in transfer item.');

            // IMPORTANT: lock sender batch in sender shop
            $senderBatch = Batch::query()
                ->where('id', (int)$item->batch_id)
                ->where('shop_id', (int)$senderBranch->id)
                ->lockForUpdate()
                ->first();

            abort_if(!$senderBatch, 422, 'Batch missing for one of the transfer items.');
            abort_if((int)$senderBatch->quantity < $qty, 422, "Sender stock insufficient for barcode {$senderBatch->barcode}.");

            // deduct sender
            $senderBatch->quantity = (int)$senderBatch->quantity - $qty;
            $senderBatch->save();

            // receiver batch by barcode
            $receiverBatch = Batch::query()
                ->where('shop_id', (int)$receiverBranch->id)
                ->where('barcode', $senderBatch->barcode)
                ->lockForUpdate()
                ->first();

            if (!$receiverBatch) {
                $receiverBatch = Batch::create([
                    'perfume_id' => $senderBatch->perfume_id,
                    'shop_id'    => (int)$receiverBranch->id,
                    'barcode'    => $senderBatch->barcode,
                    'batch_no'   => $senderBatch->batch_no,
                    'quantity'   => 0,
                    'cost_price' => $senderBatch->cost_price, // ✅ AS-IS
                    'sell_price' => $senderBatch->sell_price,
                    'mfg_date'   => $senderBatch->mfg_date,
                    'exp_date'   => $senderBatch->exp_date,
                    'is_active'  => true,
                ]);
            } else {
                // keep prices same as sender (AS-IS)
                $receiverBatch->cost_price = $senderBatch->cost_price;
                $receiverBatch->sell_price = $senderBatch->sell_price;
                $receiverBatch->save();
            }

            $receiverBatch->increment('quantity', $qty);

            $cost = (float)($senderBatch->cost_price ?? 0);
            $costTotal += ($cost * $qty);
        }

        $costTotal = round($costTotal, 2);

        // Accounts posting — prevent duplicates
        $refType = 'branch_transfer_claim';
        $refId = (int)$transfer->id;

        $already = AccountEntry::query()
            ->where('ref_type', $refType)
            ->where('ref_id', $refId)
            ->exists();

        if (!$already) {
            $senderAccount   = $this->firstAccountOrFail((int)$senderBranch->id);
            $receiverAccount = $this->firstAccountOrFail((int)$receiverBranch->id);

            $desc = "Branch→Branch transfer claimed {$transfer->code}: {$senderBranch->name} → {$receiverBranch->name}";

            // Receiver: DEBIT
            AccountEntry::create([
                'account_id'  => $receiverAccount->id,
                'shop_id'     => (int)$receiverBranch->id,
                'user_id'     => auth()->id(),
                'entry_date'  => now()->toDateString(),
                'debit'       => $costTotal,
                'credit'      => 0,
                'description' => $desc,
                'ref_type'    => $refType,
                'ref_id'      => $refId,
            ]);

            // Sender: CREDIT
            AccountEntry::create([
                'account_id'  => $senderAccount->id,
                'shop_id'     => (int)$senderBranch->id,
                'user_id'     => auth()->id(),
                'entry_date'  => now()->toDateString(),
                'debit'       => 0,
                'credit'      => $costTotal,
                'description' => $desc,
                'ref_type'    => $refType,
                'ref_id'      => $refId,
            ]);
        }
    }
}