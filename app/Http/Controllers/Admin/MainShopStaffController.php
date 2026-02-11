<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MainShopStaffController extends Controller
{
    private function mainShopOrFail(): Shop
    {
        $main = Shop::where('type', 'main')->first();
        abort_if(!$main, 404, 'Main shop not found.');
        return $main;
    }

    public function index()
    {
        $shop = $this->mainShopOrFail();

        $staff = User::where('shop_id', $shop->id)
            ->where('role', 'staff')
            ->orderByDesc('id')
            ->get();

        return view('admin.mainshop.staff.index', compact('shop', 'staff'));
    }

    public function create()
    {
        $shop = $this->mainShopOrFail();
        return view('admin.mainshop.staff.create', compact('shop'));
    }

    public function store(Request $request)
    {
        $shop = $this->mainShopOrFail();

        $data = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
        ]);

        $plainPassword = 'St@' . Str::random(8);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($plainPassword),
            'password_text' => $plainPassword,
            'role' => 'staff',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $request->validate([
    'monthly_salary' => ['nullable','numeric','min:0'],
    'work_hours_per_day' => ['required','integer','min:1','max:24'],
]);

$monthly = $request->monthly_salary !== null ? (float)$request->monthly_salary : null;
$workHours = (int)$request->work_hours_per_day;

$daily = null;
$hourly = null;

if ($monthly !== null && $monthly > 0) {
    $daily = round($monthly / 30, 2);
    $hourly = $workHours > 0 ? round($daily / $workHours, 4) : 0;
}

$user->monthly_salary = $monthly;
$user->work_hours_per_day = $workHours;
$user->daily_salary = $daily;
$user->hourly_salary = $hourly;

$user->save();

        return redirect()
            ->route('admin.mainshop.staff.index')
            ->with('success', "Main shop staff created. Email: {$user->email} Password: {$plainPassword}");
    }
}
