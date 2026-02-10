<?php

namespace App\Http\Controllers\MainShop;

use App\Http\Controllers\Controller;
use App\Models\Perfume;
use Illuminate\Http\Request;

class PerfumeController extends Controller
{
    public function index()
    {
        $perfumes = Perfume::orderByDesc('id')->get();
        return view('panels.main.perfumes.index', compact('perfumes'));
    }

    public function create()
    {
        return view('panels.main.perfumes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'brand' => ['nullable','string','max:255'],
            'sku' => ['nullable','string','max:255','unique:perfumes,sku'],
            'description' => ['nullable','string'],
        ]);

        Perfume::create([
            'name' => $data['name'],
            'brand' => $data['brand'] ?? null,
            'sku' => $data['sku'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('main.perfumes.index')->with('success', 'Perfume created.');
    }

    public function edit(\App\Models\Perfume $perfume)
{
    return view('panels.main.perfumes.edit', compact('perfume'));
}

public function update(Request $request, \App\Models\Perfume $perfume)
{
    $data = $request->validate([
        'name' => ['required','string','max:255'],
        'brand' => ['nullable','string','max:255'],
        'sku' => ['nullable','string','max:255','unique:perfumes,sku,' . $perfume->id],
        'description' => ['nullable','string'],
        'is_active' => ['nullable'],
    ]);

    $perfume->update([
        'name' => $data['name'],
        'brand' => $data['brand'] ?? null,
        'sku' => $data['sku'] ?? null,
        'description' => $data['description'] ?? null,
        'is_active' => $request->has('is_active'),
    ]);

    return redirect()->route('main.perfumes.index')->with('success', 'Perfume updated.');
}

}
