<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PurchaseController extends Controller
{
    public function index()
    {
        return Purchase::query()
            ->with('supplier')
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request, PurchaseService $purchaseService)
    {
        $data = $request->validate([
            'purchase_no' => ['required', 'string', 'max:50', 'unique:purchases,purchase_no'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'status' => ['nullable', 'string', 'in:received,draft'],
            'notes' => ['nullable', 'string'],
            'purchased_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sparepart_id' => ['required', 'exists:spareparts,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $purchase = $purchaseService->create($data, $request->user());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($purchase, 201);
    }

    public function show(Purchase $purchase)
    {
        return $purchase->load('items.sparepart', 'supplier', 'user');
    }

    public function receive(Purchase $purchase, PurchaseService $purchaseService, Request $request)
    {
        try {
            $purchase = $purchaseService->receive($purchase, $request->user());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($purchase);
    }
}
