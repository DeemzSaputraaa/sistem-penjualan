<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function store(Request $request, StockService $stockService)
    {
        $data = $request->validate([
            'sparepart_id' => ['required', 'exists:spareparts,id'],
            'qty' => ['required', 'integer', 'not_in:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $sparepart = Sparepart::findOrFail($data['sparepart_id']);
        $delta = (int) $data['qty'];

        $stockService->adjust(
            $sparepart,
            $delta,
            'adjust',
            'adjustment',
            null,
            $request->user()?->id,
            $data['notes'] ?? null,
            null
        );

        return response()->json($sparepart->fresh(), 200);
    }
}
