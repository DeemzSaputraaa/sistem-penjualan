<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Services\StockService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StockAdjustmentController extends Controller
{
    public function store(Request $request, StockService $stockService)
    {
        $data = $request->validate([
            'sparepart_id' => ['required', 'exists:spareparts,id'],
            'qty' => ['required', 'integer', 'not_in:0'],
            'notes' => ['required', 'string', 'max:255'],
        ]);

        $sparepart = Sparepart::findOrFail($data['sparepart_id']);
        $delta = (int) $data['qty'];
        $absDelta = abs($delta);

        if ($absDelta > 50 && ! $request->user()?->hasAnyRole(['super-admin', 'admin'])) {
            return response()->json([
                'message' => 'Penyesuaian di atas 50 unit membutuhkan approval Admin/Super Admin.',
            ], 422);
        }

        try {
            $stockService->adjust(
                $sparepart,
                $delta,
                'adjust',
                'adjustment',
                null,
                $request->user(),
                $data['notes'] ?? null,
                null
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($sparepart->fresh(), 200);
    }
}
