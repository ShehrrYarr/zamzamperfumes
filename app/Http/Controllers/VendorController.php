<?php

namespace App\Http\Controllers;

use App\Models\Accounts;

use App\Models\vendor;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\AccessoryBatch;
use App\Models\Accessory;
use Carbon\Carbon;



class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function showVendors()
    {
        $vendors = vendor::with('creator')->get(); 
        // dd($vendors);
        return view('showVendors', compact('vendors'));
    }


    public function storeVendor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'office_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'mobile_no' => 'required|string|max:20',
            'CNIC' => 'nullable|string|max:25',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $userId = auth()->user()->id;
        // dd($userId);

        if (vendor::where('name', $validated['name'])->exists()) {
            return redirect()->back()->withInput()->withErrors([
                'name' => 'Vendor with this name already exists.',
            ]);
        }

        if (vendor::where('mobile_no', $validated['mobile_no'])->exists()) {
            return redirect()->back()->withInput()->withErrors([
                'mobile_no' => 'Vendor with this mobile number already exists.',
            ]);
        }

        if (!empty($validated['CNIC']) && vendor::where('CNIC', $validated['CNIC'])->exists()) {
            return redirect()->back()->withInput()->withErrors([
                'CNIC' => 'Vendor with this CNIC already exists.',
            ]);
        }

        $filePath = null;
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $filePath = $file->store('vendor_pictures', 'public');
        }

        vendor::create([
            'name' => $validated['name'],
            'office_address' => $validated['office_address'],
            'city' => $validated['city'],
            'mobile_no' => $validated['mobile_no'],
            'CNIC' => $validated['CNIC'],
            'picture' => $filePath,
            'created_by' => $userId, // 👈 track who added the vendor
        ]);

        return redirect()->back()->with('success', 'Vendor added successfully!');
    }



    public function editVendor($id)
    {
        $filterId = vendor::find($id);
        // dd($filterId);
        if (!$filterId) {

            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);

    }

    public function updateVendor(Request $request)
    {
        $data = vendor::findOrFail($request->id);

        // Delete old picture if a new one is uploaded
        if ($request->hasFile('picture')) {
            // Check and delete existing picture
            if ($data->picture && Storage::disk('public')->exists($data->picture)) {
                Storage::disk('public')->delete($data->picture);
            }

            // Store new picture
            $path = $request->file('picture')->store('vendor_pictures', 'public');
            $data->picture = $path;
        }

        // Update other vendor data
        $data->name = $request->input('name');
        $data->city = $request->input('city');
        $data->office_address = $request->input('office_address');
        $data->mobile_no = $request->input('mobile_no');
        $data->CNIC = $request->input('CNIC');

        $data->save();

        return redirect()->back()->with('success', 'Vendor updated successfully.');
    }


    public function destroyVendor(Request $request)
    {
        $vendor = vendor::findOrFail($request->id);

        // Delete picture from storage if it exists
        if ($vendor->picture && Storage::disk('public')->exists($vendor->picture)) {
            Storage::disk('public')->delete($vendor->picture);
        }

        // Delete the vendor record
        $vendor->delete();

        return redirect()->back()->with('success', 'Vendor deleted successfully!');
    }

    
    

    
    

    public function getBalance(Request $request)
    {
        $vendorId = $request->vendor_id;

        $credit = Accounts::where('vendor_id', $vendorId)->where('category', 'CR')->sum('amount');
        $debit = Accounts::where('vendor_id', $vendorId)->where('category', 'DB')->sum('amount');

        $balance = $credit - $debit;

        return response()->json([
            'balance' => abs($balance),
            'status' => $balance < 0 ? 'Debit' : ($balance > 0 ? 'Credit' : 'Settled')
        ]);
    }


    // public function listReceivables()
    // {
    //     // Get all vendors with their total debit and credit
    //     $vendorReceivables = DB::table('accounts')
    //         ->select(
    //             'vendor_id',
    //             DB::raw("
    //             SUM(CASE WHEN category = 'DB' THEN amount ELSE 0 END) AS total_debit,
    //             SUM(CASE WHEN category = 'CR' THEN amount ELSE 0 END) AS total_credit
    //         ")
    //         )
    //         ->whereNotNull('vendor_id')
    //         ->groupBy('vendor_id')
    //         ->get();

    //     // Filter out the vendors who owe you (total_debit > total_credit)
    //     $vendorsOwing = $vendorReceivables->filter(function ($vendor) {
    //         return ($vendor->total_debit - $vendor->total_credit) > 0;
    //     });

    //     // Get the actual vendor details from the 'vendors' table
    //     $vendorsOwingDetails = Vendor::whereIn('id', $vendorsOwing->pluck('vendor_id'))
    //         ->get();

    //     return view('receivableVendors', compact('vendorsOwingDetails'));
    // }

    public function listReceivables()
{
    // 1. Get all vendors with their total debit and credit
    $vendorReceivables = DB::table('accounts')
        ->select(
            'vendor_id',
            DB::raw("SUM(Debit) AS total_debit"),
            DB::raw("SUM(Credit) AS total_credit")
        )
        ->whereNotNull('vendor_id')
        ->groupBy('vendor_id')
        ->get();

    // 2. Filter to vendors who owe YOU (total_debit > total_credit)
    $vendorsOwing = $vendorReceivables->filter(function ($vendor) {
        return ($vendor->total_debit - $vendor->total_credit) > 0;
    });

    // 3. Get the vendor details
    $vendorsOwingDetails = \App\Models\vendor::whereIn('id', $vendorsOwing->pluck('vendor_id'))
        ->get();

    // 4. Attach amount owed
    $vendorsOwingDetails = $vendorsOwingDetails->map(function ($vendor) use ($vendorReceivables) {
        $vendorReceivable = $vendorReceivables->firstWhere('vendor_id', $vendor->id);
        $vendor->amount_owed = $vendorReceivable->total_debit - $vendorReceivable->total_credit;
        return $vendor;
    });

    return view('receivableVendors', compact('vendorsOwingDetails'));
}

    

public function getVBalance($id)
{
    $vendor = \App\Models\vendor::findOrFail($id);

    // Calculate balance: sum(Credit) - sum(Debit)
    $balance = \App\Models\Accounts::where('vendor_id', $id)
        ->selectRaw('COALESCE(SUM(Credit),0) - COALESCE(SUM(Debit),0) as balance')
        ->value('balance');

    return response()->json([
        'balance' => $balance,
        'vendor_name' => $vendor->name,
    ]);
}

  public function showVSHistory(Request $request, $id)
    {
        $vendor = vendor::findOrFail($id);

        // Optional date filters: ?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
        $query = Sale::with(['items.batch.accessory', 'user'])
            ->where('vendor_id', $vendor->id)
            ->orderByDesc('sale_date');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = $request->input('start_date') . ' 00:00:00';
            $end   = $request->input('end_date')   . ' 23:59:59';
            $query->whereBetween('sale_date', [$start, $end]);
        }

        $sales = $query->get();

        // Totals across the filtered sales
        $totalSoldAmount = $sales->sum('total_amount'); // after discount
        $totalPaidAmount = $sales->sum(function ($sale) {
            // For vendor sales, we record pay_amount
            return (float) ($sale->pay_amount ?? 0);
        });
        $totalRemaining  = max($totalSoldAmount - $totalPaidAmount, 0);

        // ---- Breakdown by Accessory (what this vendor bought) ----
        // qty, gross (before discount per-line), avg unit price, last sold date
        $byAccessory = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $acc   = $item->batch->accessory ?? null;
                if (!$acc) continue;

                $key = $acc->id;
                if (!isset($byAccessory[$key])) {
                    $byAccessory[$key] = [
                        'accessory_id' => $acc->id,
                        'name'         => $acc->name,
                        'qty'          => 0,
                        'gross'        => 0.0, // sum of line subtotals (before cart-level discount allocation)
                        'avg_price'    => 0.0,
                        'last_sold_at' => $sale->sale_date,
                    ];
                }

                $byAccessory[$key]['qty']   += (int) $item->quantity;
                $byAccessory[$key]['gross'] += (float) $item->subtotal;

                // Track latest sale date
                if ($sale->sale_date > $byAccessory[$key]['last_sold_at']) {
                    $byAccessory[$key]['last_sold_at'] = $sale->sale_date;
                }
            }
        }
        // Compute average price per accessory (gross / qty)
        foreach ($byAccessory as &$accRow) {
            $accRow['avg_price'] = $accRow['qty'] > 0 ? round($accRow['gross'] / $accRow['qty'], 2) : 0.0;
        }
        unset($accRow);

        // ---- Breakdown by Batch (which batches were sold to this vendor) ----
        // qty bought by this vendor from each batch, unit price used on sale lines
        $byBatch = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $batch = $item->batch;
                if (!$batch) continue;

                $key = $batch->id;
                if (!isset($byBatch[$key])) {
                    $byBatch[$key] = [
                        'batch_id'      => $batch->id,
                        'barcode'       => $batch->barcode,
                        'accessory'     => optional($batch->accessory)->name,
                        'qty_sold'      => 0,
                        'unit_price'    => (float) $item->price_per_unit, // price used in sale
                        'line_gross'    => 0.0, // sum of (unit_price * qty) to this vendor
                        'purchase_date' => $batch->purchase_date,
                        'qty_remaining' => $batch->qty_remaining, // current stock (global)
                    ];
                }

                $byBatch[$key]['qty_sold']   += (int) $item->quantity;
                $byBatch[$key]['line_gross'] += (float) $item->subtotal;
                // If prices vary across lines, keep the last seen price_per_unit
                $byBatch[$key]['unit_price']  = (float) $item->price_per_unit;
            }
        }

        // Sort breakdowns (optional): most bought first
        $byAccessory = collect($byAccessory)->sortByDesc('qty')->values();
        $byBatch     = collect($byBatch)->sortByDesc('qty_sold')->values();

        return view('showVSHistory', [
            'vendor'           => $vendor,
            'sales'            => $sales,
            'totalSoldAmount'  => $totalSoldAmount,
            'totalPaidAmount'  => $totalPaidAmount,
            'totalRemaining'   => $totalRemaining,
            'byAccessory'      => $byAccessory,
            'byBatch'          => $byBatch,
            'start_date'       => $request->input('start_date'),
            'end_date'         => $request->input('end_date'),
        ]);
    }

    public function showVRHistory(Request $request, $id)
{
    $vendor = vendor::findOrFail($id); // ⬅️ if your model is actually \App\Models\vendor, swap to that

    // Base query: all batches supplied by this vendor
    $query = AccessoryBatch::with(['accessory', 'user'])
        ->where('vendor_id', $vendor->id);

    // Optional date filter by purchase_date (YYYY-MM-DD)
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $start = Carbon::parse($request->input('start_date'))->startOfDay()->toDateString();
        $end   = Carbon::parse($request->input('end_date'))->endOfDay()->toDateString();
        $query->whereBetween('purchase_date', [$start, $end]);
    }

    $batches = $query->orderByDesc('purchase_date')->orderByDesc('id')->get();

    // ---- Totals (for summary cards) ----
    $totalQtyPurchased = (int) $batches->sum('qty_purchased');
    $totalQtyRemaining = (int) $batches->sum('qty_remaining');

    // Total purchase cost = sum(qty_purchased * purchase_price)
    $totalPurchaseCost = $batches->reduce(function($carry, $b) {
        return $carry + ((float)$b->purchase_price * (int)$b->qty_purchased);
    }, 0.0);

    // Potential retail value at the time (nullable selling_price handled as 0)
    $totalPotentialRetail = $batches->reduce(function($carry, $b) {
        $sell = $b->selling_price !== null ? (float)$b->selling_price : 0.0;
        return $carry + ($sell * (int)$b->qty_purchased);
    }, 0.0);

    // ---- Breakdown by Accessory ----
    // Aggregate quantities & prices per accessory
    $byAccessory = [];
    foreach ($batches as $batch) {
        $acc = $batch->accessory;
        if (!$acc) continue;

        $key = $acc->id;
        if (!isset($byAccessory[$key])) {
            $byAccessory[$key] = [
                'accessory_id'   => $acc->id,
                'name'           => $acc->name,
                'batches_count'  => 0,
                'qty_purchased'  => 0,
                'qty_remaining'  => 0,
                'total_cost'     => 0.0,  // sum(qty_purchased * purchase_price)
                'avg_purchase'   => 0.0,
                'avg_selling'    => 0.0,
                'last_purchase'  => $batch->purchase_date,
            ];
        }

        $row = &$byAccessory[$key];
        $row['batches_count'] += 1;
        $row['qty_purchased'] += (int)$batch->qty_purchased;
        $row['qty_remaining'] += (int)$batch->qty_remaining;
        $row['total_cost']    += (float)$batch->purchase_price * (int)$batch->qty_purchased;

        // track latest purchase date
        if ($batch->purchase_date > $row['last_purchase']) {
            $row['last_purchase'] = $batch->purchase_date;
        }

        // keep running sums for average prices
        // we'll compute averages after the loop using all batches under this accessory
        unset($row);
    }

    // Compute purchase/selling averages per accessory
    foreach ($byAccessory as $accId => &$row) {
        $relatedBatches = $batches->filter(fn($b) => optional($b->accessory)->id === $accId);

        $purchaseAvg = $relatedBatches->avg('purchase_price') ?? 0.0;
        // selling_price can be null
        $sellingAvg  = $relatedBatches->filter(fn($b) => $b->selling_price !== null)->avg('selling_price') ?? 0.0;

        $row['avg_purchase'] = round((float)$purchaseAvg, 2);
        $row['avg_selling']  = round((float)$sellingAvg, 2);
    }
    unset($row);

    // Sort breakdowns (optional): most purchased first
    $byAccessory = collect($byAccessory)->sortByDesc('qty_purchased')->values();

    return view('showVRHistory', [
        'vendor'               => $vendor,
        'batches'              => $batches,
        'totalQtyPurchased'    => $totalQtyPurchased,
        'totalQtyRemaining'    => $totalQtyRemaining,
        'totalPurchaseCost'    => $totalPurchaseCost,
        'totalPotentialRetail' => $totalPotentialRetail,
        'byAccessory'          => $byAccessory,
        'start_date'           => $request->input('start_date'),
        'end_date'             => $request->input('end_date'),
    ]);
}



}
