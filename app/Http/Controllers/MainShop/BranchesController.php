<?php

namespace App\Http\Controllers\MainShop;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class BranchesController extends Controller
{
    public function index()
    {
        $branches = Shop::where('type', 'branch')
            ->orderBy('name')
            ->get();

        return view('panels.main.branches.index', compact('branches'));
    }
}
