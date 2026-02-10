<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    private function branchIdOrFail(): int
    {
        $user = Auth::user();
        abort_if(!$user || $user->role !== 'branch_shop', 403);
        abort_if(!$user->shop_id, 403, 'Branch shop_id missing.');
        return (int) $user->shop_id;
    }

    public function index()
    {
        $branchId = $this->branchIdOrFail();

        $staff = User::where('shop_id', $branchId)
            ->where('role', 'staff')
            ->orderByDesc('id')
            ->get();

        return view('panels.branch.staff.index', compact('staff'));
    }

    public function create()
    {
        $this->branchIdOrFail();
        return view('panels.branch.staff.create');
    }

    public function store(Request $request)
    {
        $branchId = $this->branchIdOrFail();

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
            'shop_id' => $branchId,
            'is_active' => true,
        ]);

        return redirect()
            ->route('branch.staff.index')
            ->with('success', "Staff created. Email: {$user->email} Password: {$plainPassword}");
    }

    public function toggle(User $user)
    {
        $branchId = $this->branchIdOrFail();

        // ✅ Critical: prevent toggling staff of other branches
        abort_if($user->role !== 'staff', 404);
        abort_if((int)$user->shop_id !== $branchId, 403);

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Staff status updated.');
    }
}
