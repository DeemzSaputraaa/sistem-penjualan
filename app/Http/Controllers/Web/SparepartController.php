<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SparepartController extends Controller
{
    public function index()
    {
        $spareparts = Sparepart::query()
            ->with('category')
            ->orderBy('name')
            ->paginate(15);

        return view('spareparts.index', compact('spareparts'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('spareparts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:spareparts,sku'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price_buy' => ['nullable', 'numeric', 'min:0'],
            'price_sell' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        Sparepart::create($data);

        return redirect()->route('spareparts.index')->with('status', 'Sparepart berhasil ditambahkan.');
    }

    public function edit(Sparepart $sparepart)
    {
        $categories = Category::orderBy('name')->get();

        return view('spareparts.edit', compact('sparepart', 'categories'));
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('spareparts', 'sku')->ignore($sparepart->id)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price_buy' => ['nullable', 'numeric', 'min:0'],
            'price_sell' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $sparepart->update($data);

        return redirect()->route('spareparts.index')->with('status', 'Sparepart diperbarui.');
    }

    public function destroy(Sparepart $sparepart)
    {
        $sparepart->delete();

        return redirect()->route('spareparts.index')->with('status', 'Sparepart dihapus.');
    }
}
