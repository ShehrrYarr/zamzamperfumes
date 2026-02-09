<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
     public function __construct()
     {
         $this->middleware('auth');
     }

     public function index(){
        $banks = Bank::all();
        return view('banks.index', compact('banks'));

     }

     public function storeBank(Request $request){
        // dd($request->all());
        $bank = new Bank();
        $bank->name = $request->name;
        $bank->account_no = $request->account_no;
        $bank->branch = $request->branch ?? "No Branch";
        $bank->iban = $request->iban ?? "No IBAN";
        $bank->swift = $request->swift ?? "No swift";
        $bank->save();
        return redirect()->back()->with('success','Bank Stored Successfully');
     }

     public function getBank($id)
    {
        $filterId = Bank::find($id);
        // dd($filterId);
        if (!$filterId) {

            return response()->json(['message' => 'Id not found'], 404);
        }

        return response()->json(['result' => $filterId]);

    }

    public function updateBank(Request $request){
        // dd($request->all());
        $bank = Bank::find($request->id);
         $bank->name = $request->name;
        $bank->account_no = $request->account_no;
        $bank->branch = $request->branch;
        $bank->save();
        return redirect()->back()->with('success', 'Bank Updated Successfully');
    }
}
