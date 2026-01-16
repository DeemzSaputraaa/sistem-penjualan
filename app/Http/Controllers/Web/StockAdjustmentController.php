<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function create()
    {
        $spareparts = Sparepart::orderBy('name')->get();

        return view('stock-adjustments.create', compact('spareparts'));
    }

    public function store(Request $request, StockService $stockService)
    {
        $data = $request->validate([
            'sparepart_id' => ['required', 'exists:spareparts,id'],
            'qty' => ['required', 'integer', 'not_in:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $sparepart = Sparepart::findOrFail($data['sparepart_id']);
        $stockService->adjust(
            $sparepart,
            (int) $data['qty'],
            'adjust',
            'adjustment',
            null,
            $request->user()->id,
            $data['notes'] ?? null,
            null
        );

        return redirect()->route('stock-adjustments.create')->with('status', 'Stok berhasil disesuaikan.');
    }
}
