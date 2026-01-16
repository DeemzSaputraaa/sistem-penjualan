<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SaleController extends Controller
{
    public function index()
    {
        return Sale::query()
            ->with('customer')
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request, SaleService $saleService)
    {
        $data = $request->validate([
            'invoice_no' => ['required', 'string', 'max:50', 'unique:sales,invoice_no'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'sold_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sparepart_id' => ['required', 'exists:spareparts,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $sale = $saleService->create($data, $request->user());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($sale, 201);
    }

    public function show(Sale $sale)
    {
        return $sale->load('items.sparepart', 'customer', 'user');
    }
}
