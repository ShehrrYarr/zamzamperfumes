<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Shop::where('type', 'branch')
            ->orderByDesc('id')
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'code'    => ['required','string','max:50','unique:shops,code'],
            'address' => ['nullable','string','max:255'],
            'email'   => ['required','email','max:255','unique:users,email'],
        ]);

        // 1) create branch shop
        $shop = Shop::create([
            'name' => $data['name'],
            'type' => 'branch',
            'code' => $data['code'],
            'address' => $data['address'] ?? null,
            'is_active' => true,
        ]);

        // 2) create branch login (branch_shop)
        $plainPassword = Str::random(10) . '@1'; // readable-ish
        $user = User::create([
            'name' => $shop->name . ' Login',
            'email' => $data['email'],
            'password' => Hash::make($plainPassword),
            'password_text' => $plainPassword, // you have this column
            'role' => 'branch_shop',
            'shop_id' => $shop->id,
        ]);

        return redirect()
            ->route('admin.branches.index')
            ->with('success', "Branch created. Login email: {$user->email} Password: {$plainPassword}");
    }

    public function toggle(Shop $shop)
    {
        if ($shop->type !== 'branch') {
            abort(404);
        }

        $shop->is_active = !$shop->is_active;
        $shop->save();

        return back()->with('success', 'Branch status updated.');
    }

    public function edit(Shop $shop)
{
    if ($shop->type !== 'branch') {
        abort(404);
    }

    // Branch login user (role: branch_shop) for this shop
    $branchLogin = User::where('shop_id', $shop->id)
        ->where('role', 'branch_shop')
        ->first();

    return view('admin.branches.edit', compact('shop', 'branchLogin'));
}

public function update(Request $request, Shop $shop)
{
    if ($shop->type !== 'branch') {
        abort(404);
    }

    $data = $request->validate([
        'name'    => ['required','string','max:255'],
        'code'    => ['required','string','max:50','unique:shops,code,' . $shop->id],
        'address' => ['nullable','string','max:255'],

        // optional: update branch login email
        'email'   => ['nullable','email','max:255'],
    ]);

    $shop->update([
        'name' => $data['name'],
        'code' => $data['code'],
        'address' => $data['address'] ?? null,
    ]);

    // If admin provided email, update the branch login email too
    if (!empty($data['email'])) {
        $branchLogin = User::where('shop_id', $shop->id)
            ->where('role', 'branch_shop')
            ->first();

        if ($branchLogin) {
            // Ensure unique email excluding current user
            $request->validate([
                'email' => ['unique:users,email,' . $branchLogin->id],
            ]);

            $branchLogin->email = $data['email'];
            $branchLogin->save();
        }
    }

    return redirect()
        ->route('admin.branches.index')
        ->with('success', 'Branch updated successfully.');
}
public function resetLoginPassword(Shop $shop)
{
    if ($shop->type !== 'branch') {
        abort(404);
    }

    $branchLogin = User::where('shop_id', $shop->id)
        ->where('role', 'branch_shop')
        ->first();

    if (!$branchLogin) {
        return back()->with('success', 'Branch login user not found for this branch.');
    }

    $newPassword = 'Br@' . Str::random(8); // readable-ish
    $branchLogin->password = Hash::make($newPassword);

    // since you have this column:
    if (isset($branchLogin->password_text)) {
        $branchLogin->password_text = $newPassword;
    }

    $branchLogin->save();

    return back()->with('success', "Branch login password reset. Email: {$branchLogin->email} New Password: {$newPassword}");
}
}
