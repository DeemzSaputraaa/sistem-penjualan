<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SparepartController extends Controller
{
    public function index()
    {
        return Sparepart::query()
            ->with('category')
            ->orderBy('name')
            ->paginate(20);
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

        $sparepart = Sparepart::create($data);

        return response()->json($sparepart->load('category'), 201);
    }

    public function show(Sparepart $sparepart)
    {
        return $sparepart->load('category');
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

        $sparepart->update($data);

        return $sparepart->load('category');
    }

    public function destroy(Sparepart $sparepart)
    {
        $sparepart->delete();

        return response()->noContent();
    }
}
