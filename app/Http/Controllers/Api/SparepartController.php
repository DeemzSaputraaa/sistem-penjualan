<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        return response()->json($sparepart->load('category'), 201);
    }

    public function show(Sparepart $sparepart)
    {
        return $sparepart->load('category');
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

        $data['sku'] = Str::upper(trim($data['sku']));
        $data['name'] = trim($data['name']);
        $data['unit'] = isset($data['unit']) ? trim($data['unit']) : null;
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

        return $sparepart->load('category');
    }

    public function destroy(Sparepart $sparepart, AuditLogService $auditLog, Request $request)
    {
        $before = $sparepart->only(['sku', 'name', 'category_id', 'unit', 'price_buy', 'price_sell', 'min_stock', 'is_active']);
        $sparepart->delete();

        $auditLog->log('sparepart.delete', $sparepart, $request->user(), $request, $before, null);

        return response()->noContent();
    }
}
