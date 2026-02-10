<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MainShopController extends Controller
{
    public function show()
    {
        $mainShop = Shop::where('type', 'main')->first();

        $mainLogin = null;
        if ($mainShop) {
            $mainLogin = User::where('shop_id', $mainShop->id)
                ->where('role', 'main_shop')
                ->first();
        }

        return view('admin.mainshop.show', compact('mainShop', 'mainLogin'));
    }

    public function store(Request $request)
    {
        // enforce ONLY ONE main shop
        $existing = Shop::where('type', 'main')->first();
        if ($existing) {
            return back()->with('success', 'Main shop already exists. You can update it later (we’ll add edit next).');
        }

        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'code'    => ['required','string','max:50','unique:shops,code'],
            'address' => ['nullable','string','max:255'],
            'email'   => ['required','email','max:255','unique:users,email'],
        ]);

        $shop = Shop::create([
            'name' => $data['name'],
            'type' => 'main',
            'code' => $data['code'],
            'address' => $data['address'] ?? null,
            'is_active' => true,
        ]);

        $plainPassword = 'Main@' . Str::random(8);

        $user = User::create([
            'name' => $shop->name . ' Login',
            'email' => $data['email'],
            'password' => Hash::make($plainPassword),
            'password_text' => $plainPassword,
            'role' => 'main_shop',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.mainshop.show')
            ->with('success', "Main shop created. Login email: {$user->email} Password: {$plainPassword}");
    }

    public function resetLoginPassword()
    {
        $mainShop = Shop::where('type', 'main')->first();
        if (!$mainShop) {
            return back()->with('success', 'Main shop not found.');
        }

        $mainLogin = User::where('shop_id', $mainShop->id)
            ->where('role', 'main_shop')
            ->first();

        if (!$mainLogin) {
            return back()->with('success', 'Main shop login user not found.');
        }

        $newPassword = 'Main@' . Str::random(8);
        $mainLogin->password = Hash::make($newPassword);
        $mainLogin->password_text = $newPassword;
        $mainLogin->save();

        return back()->with('success', "Main login password reset. Email: {$mainLogin->email} New Password: {$newPassword}");
    }
}
