<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Services\StockService;
use Illuminate\Http\Request;
use InvalidArgumentException;

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
            'notes' => ['required', 'string', 'max:255'],
        ]);

        $delta = (int) $data['qty'];
        $absDelta = abs($delta);

        if ($absDelta > 50 && ! $request->user()?->hasAnyRole(['super-admin', 'admin'])) {
            return back()->withInput()->withErrors([
                'qty' => 'Penyesuaian di atas 50 unit membutuhkan approval Admin/Super Admin.',
            ]);
        }

        $sparepart = Sparepart::findOrFail($data['sparepart_id']);
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
            return back()->withInput()->withErrors(['qty' => $exception->getMessage()]);
        }

        return redirect()->route('stock-adjustments.create')->with('status', 'Stok berhasil disesuaikan.');
    }
}
