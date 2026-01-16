<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Sparepart;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SparepartController extends Controller
{
    public function index()
    {
        $filters = [
            'search' => request('search'),
            'category_id' => request('category_id'),
        ];

        $spareparts = Sparepart::query()
            ->with('category')
            ->when($filters['search'], function ($query, string $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['category_id'], function ($query, string $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('spareparts.index', compact('spareparts', 'categories', 'filters'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('spareparts.create', compact('categories'));
    }

    public function store(Request $request, AuditLogService $auditLog)
    {
        if ($request->has('sku')) {
            $request->merge(['sku' => Str::upper(trim($request->input('sku')))]);
        }

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

        $data['sku'] = Str::upper(trim($data['sku']));
        $data['name'] = trim($data['name']);
        $data['unit'] = isset($data['unit']) ? trim($data['unit']) : null;
        $data['is_active'] = $request->boolean('is_active');

        if (($data['price_sell'] ?? 0) < ($data['price_buy'] ?? 0)) {
            return back()->withInput()->withErrors([
                'price_sell' => 'Harga jual tidak boleh lebih kecil dari harga beli.',
            ]);
        }

        $sparepart = Sparepart::create($data);

        $auditLog->log(
            'sparepart.create',
            $sparepart,
            $request->user(),
            $request,
            null,
            $sparepart->only([
                'sku',
                'name',
                'category_id',
                'unit',
                'price_buy',
                'price_sell',
                'min_stock',
                'is_active',
            ])
        );

        return redirect()->route('spareparts.index')->with('status', 'Sparepart berhasil ditambahkan.');
    }

    public function edit(Sparepart $sparepart)
    {
        $categories = Category::orderBy('name')->get();

        return view('spareparts.edit', compact('sparepart', 'categories'));
    }

    public function update(Request $request, Sparepart $sparepart, AuditLogService $auditLog)
    {
        if ($request->has('sku')) {
            $request->merge(['sku' => Str::upper(trim($request->input('sku')))]);
        }

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

        $data['sku'] = Str::upper(trim($data['sku']));
        $data['name'] = trim($data['name']);
        $data['unit'] = isset($data['unit']) ? trim($data['unit']) : null;
        $data['is_active'] = $request->boolean('is_active');

        if (($data['price_sell'] ?? 0) < ($data['price_buy'] ?? 0)) {
            return back()->withInput()->withErrors([
                'price_sell' => 'Harga jual tidak boleh lebih kecil dari harga beli.',
            ]);
        }

        $before = $sparepart->only([
            'sku',
            'name',
            'category_id',
            'unit',
            'price_buy',
            'price_sell',
            'min_stock',
            'is_active',
        ]);

        $sparepart->update($data);

        $auditLog->log(
            'sparepart.update',
            $sparepart,
            $request->user(),
            $request,
            $before,
            $sparepart->only([
                'sku',
                'name',
                'category_id',
                'unit',
                'price_buy',
                'price_sell',
                'min_stock',
                'is_active',
            ])
        );

        return redirect()->route('spareparts.index')->with('status', 'Sparepart diperbarui.');
    }

    public function destroy(Sparepart $sparepart, AuditLogService $auditLog, Request $request)
    {
        $before = $sparepart->only(['sku', 'name', 'category_id', 'unit', 'price_buy', 'price_sell', 'min_stock', 'is_active']);
        $sparepart->delete();

        $auditLog->log('sparepart.delete', $sparepart, $request->user(), $request, $before, null);

        return redirect()->route('spareparts.index')->with('status', 'Sparepart dihapus.');
    }
}
