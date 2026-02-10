<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Models\LoginHistory;
use Jenssegers\Agent\Agent;


class HomeController extends Controller
{


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
   public function index(Request $request)
{
    $user = Auth::user();

    // If not logged in, show public home (or redirect to login)
    if (!$user) {
        $totalUsers = User::count();
        return view('home', compact('totalUsers'));
    }

    // Optional: log login history ONCE per login (your code logs every hit to /home)
    // For now keep it simple: only create if not created recently (basic protection)

    // ✅ Role-based redirect (NEW SYSTEM)
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'main_shop') {
        return redirect()->route('main.dashboard');
    }

    if ($user->role === 'branch_shop') {
        return redirect()->route('branch.dashboard');
    }

    return redirect()->route('staff.dashboard');
}
    public function logout(Request $request)
    {
        
        Auth::logout();
        $request->session()->invalidate();
        return redirect('/');
    }
}
