<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccessoryBatch;
use App\Models\company;
use App\Models\group;
use App\Models\MasterPassword;
use App\Models\vendor;
use App\Models\Accessory;
use App\Models\Accounts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;





class AccessoryBatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

  public function bulkCreate()
    {
        $vendors = Vendor::orderBy('name')->get(['id','name','mobile_no']);
        $accessories = Accessory::with(['company:id,name','group:id,name'])
            ->orderBy('name')
            ->get(['id','name','company_id','group_id','min_qty']);

        return view('batches.bulk', compact('vendors', 'accessories'));
    }
    


  public function index(Request $request)
{
    $vendors    = Vendor::all();          // existing (if needed elsewhere)
    $accessories= Accessory::all();       // existing (if needed elsewhere)
    $groups     = \App\Models\group::all();
    $companies  = \App\Models\company::all();

    $query = AccessoryBatch::with(['accessory', 'user', 'vendor']);

    // Date range (created_at)
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $start = $request->input('start_date') . ' 00:00:00';
        $end   = $request->input('end_date')   . ' 23:59:59';
        $query->whereBetween('created_at', [$start, $end]);
    }

    // Filter by Group (via accessory)
    if ($request->filled('group_id')) {
        $groupId = (int) $request->input('group_id');
        $query->whereHas('accessory', function ($q) use ($groupId) {
            $q->where('group_id', $groupId);
        });
    }

    // Filter by Company (via accessory)
    if ($request->filled('company_id')) {
        $companyId = (int) $request->input('company_id');
        $query->whereHas('accessory', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });
    }

    $batches = $query->orderByDesc('id')->get();

    // Sum of total purchase price (filtered!)
    $totalPurchasePrice = $batches->sum(function ($batch) {
        return $batch->qty_purchased * $batch->purchase_price;
    });

    return view('batches.index', compact(
        'batches', 'vendors', 'accessories', 'totalPurchasePrice',
        'groups', 'companies'
    ));
}




//    public function store(Request $request)
// {
//     try {
//         $validated = $request->validate([
//             'accessory_id'    => 'required|exists:accessories,id',
//             'vendor_id'       => 'required|exists:vendors,id',
//             'qty_purchased'   => 'required|integer|min:1',
//             'purchase_price'  => 'required|numeric|min:0',
//             'selling_price'   => 'required|numeric|min:0',
//             'purchase_date'   => 'required|date',
//             'description'     => 'nullable|string',
//         ]);

//         $validated['user_id'] = auth()->id();
//         $validated['qty_remaining'] = $validated['qty_purchased'];

//         // Generate a unique barcode
//         $lastId = \App\Models\AccessoryBatch::max('id') ?? 0;
//         $validated['barcode'] = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

//         $batch = \App\Models\AccessoryBatch::create($validated);

//         // Accounts logic
//         $totalAmount = $validated['qty_purchased'] * $validated['purchase_price'];
//         $payAmount = $request->input('pay_amount', 0);

//         // Credit: you owe the vendor for the batch
//         \App\Models\Accounts::create([
//             'vendor_id'   => $validated['vendor_id'],
//             'batch_id'    => $batch->id, // ✅ added
//             'Credit'      => $totalAmount,
//             'Debit'       => 0,
//             'description' => "Batch Purchase: {$batch->barcode} ({$validated['qty_purchased']} x {$validated['purchase_price']})",
//             'created_by'  => auth()->id(),
//         ]);

//         // Debit: if you paid any amount now
//         if ($payAmount > 0) {
//             sleep(1);
//             \App\Models\Accounts::create([
//                 'vendor_id'   => $validated['vendor_id'],
//                 'batch_id'    => $batch->id, // ✅ added
//                 'Credit'      => 0,
//                 'Debit'       => $payAmount,
//                 'description' => "Payment for Batch: {$batch->barcode}",
//                 'created_by'  => auth()->id(),
//             ]);
//         }

//         return redirect()->back()->with('success', 'Batch Added Successfully.');

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         throw $e;
//     } catch (\Exception $e) {
//         \Log::error('Batch creation failed: ' . $e->getMessage());
//         return redirect()->back()
//             ->withInput()
//             ->with('danger', 'An unexpected error occurred while adding the batch. Please try again.');
//     }
// }

public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'accessory_id'    => 'required|exists:accessories,id',
            'vendor_id'       => 'required|exists:vendors,id',
            'qty_purchased'   => 'required|integer|min:1',
            'purchase_price'  => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'purchase_date'   => 'required|date',
            'description'     => 'nullable|string',

            // ✅ NEW: optional manual barcode
            'barcode'         => 'nullable|string|max:50',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['qty_remaining'] = $validated['qty_purchased'];

        // ✅ NEW BARCODE LOGIC (manual OR auto)
        $manualBarcode = trim((string) $request->input('barcode', ''));
        $manualBarcode = $manualBarcode !== '' ? $manualBarcode : null;

        if ($manualBarcode) {
            // check if barcode already exists
            $exists = \App\Models\AccessoryBatch::where('barcode', $manualBarcode)->exists();
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('danger', 'This barcode already exists. Please use a unique barcode.');
            }

            $validated['barcode'] = $manualBarcode;
        } else {
            // Generate a unique barcode (same as old)
            $lastId = \App\Models\AccessoryBatch::max('id') ?? 0;
            $validated['barcode'] = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            // Extra safety: in case it already exists for any reason
            if (\App\Models\AccessoryBatch::where('barcode', $validated['barcode'])->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('danger', 'Auto-generated barcode already exists. Please try again.');
            }
        }

        // ✅ unchanged
        $batch = \App\Models\AccessoryBatch::create($validated);

        // ✅ unchanged Accounts logic
        $totalAmount = $validated['qty_purchased'] * $validated['purchase_price'];
        $payAmount = $request->input('pay_amount', 0);

        \App\Models\Accounts::create([
            'vendor_id'   => $validated['vendor_id'],
            'batch_id'    => $batch->id,
            'Credit'      => $totalAmount,
            'Debit'       => 0,
            'description' => "Batch Purchase: {$batch->barcode} ({$validated['qty_purchased']} x {$validated['purchase_price']})",
            'created_by'  => auth()->id(),
        ]);

        if ($payAmount > 0) {
            sleep(1);
            \App\Models\Accounts::create([
                'vendor_id'   => $validated['vendor_id'],
                'batch_id'    => $batch->id,
                'Credit'      => 0,
                'Debit'       => $payAmount,
                'description' => "Payment for Batch: {$batch->barcode}",
                'created_by'  => auth()->id(),
            ]);
        }

        return redirect()->back()->with('success', 'Batch Added Successfully.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    } catch (\Exception $e) {
        \Log::error('Batch creation failed: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->with('danger', 'An unexpected error occurred while adding the batch. Please try again.');
    }
}



    public function barcodeInfo($id)
{
    $batch = \App\Models\AccessoryBatch::with(['accessory', 'user','vendor'])->findOrFail($id);

    return response()->json([
        'success'     => true,
        'batch'       => [
            'barcode'       => $batch->barcode,
            'accessory'     => $batch->accessory->name ?? '',
            'vendor'        => $batch->vendor->name ?? '',
            'qty_purchased' => $batch->qty_purchased,
            'qty_remaining' => $batch->qty_remaining,
            'purchase_price'=> $batch->purchase_price,
            'selling_price'=> $batch->selling_price,
            'purchase_date' => $batch->purchase_date,
            // You can add more fields if needed
        ],
        // This will generate a SVG barcode as HTML string
        'barcode_html' => \DNS1D::getBarcodeHTML($batch->barcode, 'C128'),
    ]);
}

  

 public function bulkStore(Request $request)
{
    $request->validate([
        'vendor_id'               => 'required|exists:vendors,id',
        'pay_amount'              => 'nullable|numeric|min:0',

        // Supplier discount % on GRAND TOTAL
        'pay_percentage'          => 'nullable|numeric|min:0|max:100',

        'items'                   => 'required|array|min:1',
        'items.*.accessory_id'    => 'required|exists:accessories,id',
        'items.*.qty_purchased'   => 'required|integer|min:1',
        'items.*.purchase_price'  => 'required|numeric|min:0',
        'items.*.selling_price'   => 'required|numeric|min:0',
        'items.*.purchase_date'   => 'required|date',
        'items.*.description'     => 'nullable|string',

        // Optional barcode
        'items.*.barcode'         => 'nullable|string|max:50|distinct|unique:accessory_batches,barcode',
    ]);

    $vendorId   = (int) $request->vendor_id;
    $userId     = auth()->id();
    $items      = $request->items;

    $payAmount  = (float) ($request->pay_amount ?? 0);
    $percent    = (float) ($request->pay_percentage ?? 0);

    DB::beginTransaction();

    try {
        $grandTotal = 0.0;
        $batchCodes = [];

        foreach ($items as $row) {

            $manualBarcode = isset($row['barcode']) ? trim((string) $row['barcode']) : '';
            $manualBarcode = $manualBarcode !== '' ? $manualBarcode : null;

            $data = [
                'accessory_id'   => (int) $row['accessory_id'],
                'vendor_id'      => $vendorId,
                'qty_purchased'  => (int) $row['qty_purchased'],
                'qty_remaining'  => (int) $row['qty_purchased'],
                'purchase_price' => (float) $row['purchase_price'],
                'selling_price'  => (float) $row['selling_price'],
                'purchase_date'  => $row['purchase_date'],
                'description'    => $row['description'] ?? null,
                'user_id'        => $userId,
                'barcode'        => $manualBarcode ? $manualBarcode : (string) \Illuminate\Support\Str::uuid(),
            ];

            /** @var \App\Models\AccessoryBatch $batch */
            $batch = \App\Models\AccessoryBatch::create($data);

            // If barcode not manual -> auto-generate padded id (same style as before)
            if (!$manualBarcode) {
                $n = (int) $batch->id;
                do {
                    $candidate = str_pad($n, 5, '0', STR_PAD_LEFT);
                    $exists = \App\Models\AccessoryBatch::where('barcode', $candidate)
                        ->where('id', '!=', $batch->id)
                        ->exists();
                    $n++;
                } while ($exists);

                $batch->barcode = $candidate;
                $batch->save();
            }

            $lineTotal  = (float) $data['qty_purchased'] * (float) $data['purchase_price'];
            $grandTotal += $lineTotal;
            $batchCodes[] = $batch->barcode;

            // Credit per batch (unchanged)
            \App\Models\Accounts::create([
                'vendor_id'   => $vendorId,
                'Credit'      => $lineTotal,
                'Debit'       => 0,
                'description' => "Batch Purchase: {$batch->barcode} ({$data['qty_purchased']} × {$data['purchase_price']})",
                'created_by'  => $userId,
            ]);
        }
sleep(1);
        // ✅ Supplier discount on GRAND TOTAL
        $discountAmount = 0.0;
        if ($grandTotal > 0 && $percent > 0) {
            $discountAmount = $grandTotal * ($percent / 100);
        }

        // Net payable after discount
        $netPayable = $grandTotal - $discountAmount;
        if ($netPayable < 0) $netPayable = 0;

        // ✅ Record DISCOUNT as a Debit entry (reduces payable, not cash)
        if ($discountAmount > 0) {
            \App\Models\Accounts::create([
                'vendor_id'   => $vendorId,
                'Credit'      => 0,
                'Debit'       => $discountAmount,
                'description' => "Supplier Discount {$percent}% on Batches: " . implode(', ', $batchCodes),
                'created_by'  => $userId,
            ]);
        }

        // ✅ Record PAYMENT as Debit (cash). Cap it so you don't overpay net payable.
        $paymentDebit = min($payAmount, $netPayable);
        if ($paymentDebit > 0) {
            \App\Models\Accounts::create([
                'vendor_id'   => $vendorId,
                'Credit'      => 0,
                'Debit'       => $paymentDebit,
                'description' => "Payment for Batches: " . implode(', ', $batchCodes),
                'created_by'  => $userId,
            ]);
        }

        DB::commit();

        return response()->json([
            'status'  => 'ok',
            'message' => 'Batches stored successfully',
            'totals'  => [
                'grand_total' => $grandTotal,
                'discount'    => $discountAmount,
                'net_payable' => $netPayable,
                'paid'        => $paymentDebit,
            ],
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);

        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to store batches. Please try again.',
        ], 500);
    }
}



//     public function bulkStore(Request $request)
// {
//     $request->validate([
//         'vendor_id'               => 'required|exists:vendors,id',
//         'pay_amount'              => 'nullable|numeric|min:0',
//         'items'                   => 'required|array|min:1',

//         'items.*.accessory_id'    => 'required|exists:accessories,id',
//         'items.*.qty_purchased'   => 'required|integer|min:1',
//         'items.*.purchase_price'  => 'required|numeric|min:0',
//         'items.*.selling_price'   => 'required|numeric|min:0',
//         'items.*.purchase_date'   => 'required|date',
//         'items.*.description'     => 'nullable|string',

//         // ✅ NEW: optional barcode per item
//         // distinct => no duplicates inside the same request
//         // unique   => must not exist in DB already
//         'items.*.barcode'         => 'nullable|string|max:50|distinct|unique:accessory_batches,barcode',
//     ]);

//     $vendorId   = (int) $request->vendor_id;
//     $userId     = auth()->id();
//     $items      = $request->items;
//     $payAmount  = (float) ($request->pay_amount ?? 0);

//     DB::beginTransaction();
//     try {
//         $totalCredit = 0;
//         $batchCodes  = [];

//         foreach ($items as $row) {

//             $manualBarcode = isset($row['barcode']) ? trim((string)$row['barcode']) : '';
//             $manualBarcode = $manualBarcode !== '' ? $manualBarcode : null;

//             $data = [
//                 'accessory_id'   => (int) $row['accessory_id'],
//                 'vendor_id'      => $vendorId,
//                 'qty_purchased'  => (int) $row['qty_purchased'],
//                 'qty_remaining'  => (int) $row['qty_purchased'],
//                 'purchase_price' => (float) $row['purchase_price'],
//                 'selling_price'  => (float) $row['selling_price'],
//                 'purchase_date'  => $row['purchase_date'],
//                 'description'    => $row['description'] ?? null,
//                 'user_id'        => $userId,

//                 // If manual provided -> set it now
//                 // Else use temp UUID like your old code
//                 'barcode'        => $manualBarcode ? $manualBarcode : (string) \Illuminate\Support\Str::uuid(),
//             ];

//             /** @var \App\Models\AccessoryBatch $batch */
//             $batch = \App\Models\AccessoryBatch::create($data);

//             // If barcode not manual -> auto-generate like before (pad id),
//             // with a safety check in case someone manually used that code.
//             if (!$manualBarcode) {
//                 $n = (int) $batch->id;
//                 do {
//                     $candidate = str_pad($n, 5, '0', STR_PAD_LEFT);
//                     $exists = \App\Models\AccessoryBatch::where('barcode', $candidate)
//                         ->where('id', '!=', $batch->id)
//                         ->exists();
//                     $n++;
//                 } while ($exists);

//                 $batch->barcode = $candidate;
//                 $batch->save();
//             }

//             $lineTotal   = $data['qty_purchased'] * $data['purchase_price'];
//             $totalCredit += $lineTotal;
//             $batchCodes[] = $batch->barcode;

//             // Credit (you owe vendor for each batch) - unchanged
//             \App\Models\Accounts::create([
//                 'vendor_id'   => $vendorId,
//                 'Credit'      => $lineTotal,
//                 'Debit'       => 0,
//                 'description' => "Batch Purchase: {$batch->barcode} ({$data['qty_purchased']} × {$data['purchase_price']})",
//                 'created_by'  => $userId,
//             ]);
//         }

//         // Single Debit for the combined payment (if any) - unchanged
//         if ($payAmount > 0) {
//             \App\Models\Accounts::create([
//                 'vendor_id'   => $vendorId,
//                 'Credit'      => 0,
//                 'Debit'       => $payAmount,
//                 'description' => 'Payment for Batches: ' . implode(', ', $batchCodes),
//                 'created_by'  => $userId,
//             ]);
//         }

//         DB::commit();

//         return response()->json([
//             'status'  => 'ok',
//             'message' => 'Batches stored successfully',
//             'totals'  => [
//                 'credit' => $totalCredit,
//                 'debit'  => $payAmount,
//             ],
//         ]);
//     } catch (\Throwable $e) {
//         DB::rollBack();
//         report($e);

//         return response()->json([
//             'status'  => 'error',
//             'message' => 'Failed to store batches. Please try again.',
//         ], 500);
//     }
// }

public function edit($id)
    {
        $filterId = AccessoryBatch::find($id);
        // dd($filterId);
        if (!$filterId) {

            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);

    }
 public function update(Request $request)
{ 
    $password = $request->input('password');
        $masterPassword = MasterPassword::first();

    $accessory = \App\Models\AccessoryBatch::findOrFail($request->id);
 if ($password === $masterPassword->update_password) {
    $validated = $request->validate([
        
        'barcode' => 'nullable|string',
      
    ]);

    $accessory->update($validated);

    return redirect()->back()->with('success', 'Barcode Updated Successfully.');
 }
 else {
    return redirect()->back()->with('danger', 'Incorrect update password.');
 }
}


 public function deleteBatch(Request $request)
    {
        $account = AccessoryBatch::findOrFail($request->id);

        $password = $request->input('password');
        $masterPassword = MasterPassword::first();

        // Check against delete_password
        if ($password === $masterPassword->delete_password) {
            $account->delete();

            return redirect()->back()->with('success', 'Batch deleted successfully.');
        } else {
            return redirect()->back()->with('danger', 'Incorrect delete password.');
        }

        // Delete the vendor record

        return redirect()->back()->with('success', 'Batch deleted successfully!');
    }

    public function liveIndex(Request $request)
    {
        $today = Carbon::today();
        $start = $request->query('start_date', $today->format('Y-m-d'));
        $end   = $request->query('end_date',   $today->format('Y-m-d'));
    $vendorId  = $request->query('vendor_id');
    $groupId   = $request->query('group_id');
    $companyId = $request->query('company_id');

    $vendors   = vendor::orderBy('name')->get(['id','name']);
    $groups    = group::orderBy('name')->get(['id','name']);
    $companies = company::orderBy('name')->get(['id','name']);

    return view('batches.live', compact(
        'vendors','groups','companies','start','end','vendorId','groupId','companyId'
    ));
}

public function liveFeed(Request $request)
{
    $request->validate([
        'start_date' => 'required|date_format:Y-m-d',
        'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        'vendor_id'  => 'nullable|exists:vendors,id',
        'group_id'   => 'nullable|exists:groups,id',
        'company_id' => 'nullable|exists:companies,id',
    ]);

    $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_date.' 00:00:00');
    $end   = Carbon::createFromFormat('Y-m-d H:i:s', $request->end_date.' 23:59:59');

    $query = AccessoryBatch::with([
            'accessory:id,name,group_id,company_id',
            'accessory.group:id,name',
            'accessory.company:id,name',
            'vendor:id,name',
            'user:id,name',
        ])
        ->whereBetween('created_at', [$start, $end]);

    if ($request->filled('vendor_id')) {
        $query->where('vendor_id', $request->vendor_id);
    }

    if ($request->filled('group_id')) {
        $gid = (int) $request->group_id;
        $query->whereHas('accessory', fn($q) => $q->where('group_id', $gid));
    }

    if ($request->filled('company_id')) {
        $cid = (int) $request->company_id;
        $query->whereHas('accessory', fn($q) => $q->where('company_id', $cid));
    }

    $batches = $query->orderByDesc('created_at')->limit(300)->get();

    if ($batches->isEmpty()) {
        return response()->json([
            'success'      => true,
            'data'         => [],
            'totals'       => [
                'count' => 0,
                'qty_sum' => 0,
                'purchase_sum' => 0.00,
                'remaining_sum' => 0,
            ],
            'refreshed_at' => now()->format('H:i:s'),
        ]);
    }

    $rows = $batches->map(function ($b) {
        $qty = (int) ($b->qty_purchased ?? 0);
        $pp  = (float) ($b->purchase_price ?? 0);
        $lineTotal = $qty * $pp;

        return [
            'id'            => $b->id,
            'created_at'    => optional($b->created_at)->format('Y-m-d H:i'),
            'purchase_date' => $b->purchase_date ? Carbon::parse($b->purchase_date)->format('Y-m-d') : null,

            'accessory'     => optional($b->accessory)->name,
            'group'         => optional(optional($b->accessory)->group)->name,
            'company'       => optional(optional($b->accessory)->company)->name,
            'vendor'        => optional($b->vendor)->name,
            'user'          => optional($b->user)->name,

            'qty_purchased' => $qty,
            'qty_remaining' => (int) ($b->qty_remaining ?? 0),
            'purchase_price'=> round($pp, 2),
            'selling_price' => round((float)($b->selling_price ?? 0), 2),
            'line_total'    => round($lineTotal, 2),

            'barcode'       => $b->barcode,
            'description'   => $b->description,
        ];
    });

    $totals = [
        'count'         => $rows->count(),
        'qty_sum'       => (int) $rows->sum('qty_purchased'),
        'remaining_sum' => (int) $rows->sum('qty_remaining'),
        'purchase_sum'  => round($rows->sum('line_total'), 2),
    ];

    return response()->json([
        'success'      => true,
        'data'         => $rows,
        'totals'       => $totals,
        'refreshed_at' => now()->format('H:i:s'),
    ]);
}

  
}
