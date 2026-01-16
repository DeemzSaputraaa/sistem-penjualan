<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use Illuminate\Http\Request;

class StockLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
        ];

        $logs = StockLog::query()
            ->with(['sparepart', 'user'])
            ->when($filters['search'], function ($query, string $search) {
                $query->whereHas('sparepart', function ($sparepartQuery) use ($search) {
                    $sparepartQuery->where('sku', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['type'], function ($query, string $type) {
                $query->where('type', $type);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('stock-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }
}
