<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BranchStaffController extends Controller
{
    public function index(Shop $shop)
    {
        if ($shop->type !== 'branch') abort(404);

        $staff = User::where('shop_id', $shop->id)
            ->where('role', 'staff')
            ->orderByDesc('id')
            ->get();

        return view('admin.branches.staff.index', compact('shop', 'staff'));
    }

    public function create(Shop $shop)
    {
        if ($shop->type !== 'branch') abort(404);

        return view('admin.branches.staff.create', compact('shop'));
    }

    public function store(Request $request, Shop $shop)
    {
        if ($shop->type !== 'branch') abort(404);

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
            ->route('admin.branches.staff.index', $shop->id)
            ->with('success', "Staff created. Email: {$user->email} Password: {$plainPassword}");
    }

    public function toggle(User $user)
    {
        if ($user->role !== 'staff') abort(404);

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Staff status updated.');
    }
}
