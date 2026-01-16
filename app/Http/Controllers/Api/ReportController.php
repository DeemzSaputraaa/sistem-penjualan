<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $range = $this->dateRange($request);

        $sales = Sale::query()
            ->when($range['from'], fn ($q) => $q->whereDate('sold_at', '>=', $range['from']))
            ->when($range['to'], fn ($q) => $q->whereDate('sold_at', '<=', $range['to']))
            ->selectRaw('COUNT(*) as transactions')
            ->first();

        $totalSales = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($range['from'], fn ($q) => $q->whereDate('sales.sold_at', '>=', $range['from']))
            ->when($range['to'], fn ($q) => $q->whereDate('sales.sold_at', '<=', $range['to']))
            ->selectRaw('SUM(sale_items.qty * sale_items.price) as total_sales')
            ->value('total_sales');

        $perDay = Sale::query()
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->when($range['from'], fn ($q) => $q->whereDate('sales.sold_at', '>=', $range['from']))
            ->when($range['to'], fn ($q) => $q->whereDate('sales.sold_at', '<=', $range['to']))
            ->selectRaw('DATE(sales.sold_at) as date, COUNT(DISTINCT sales.id) as transactions, SUM(sale_items.qty * sale_items.price) as total_sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $items = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($range['from'], fn ($q) => $q->whereDate('sales.sold_at', '>=', $range['from']))
            ->when($range['to'], fn ($q) => $q->whereDate('sales.sold_at', '<=', $range['to']))
            ->selectRaw('SUM(sale_items.qty) as total_items')
            ->value('total_items');

        return response()->json([
            'range' => $range,
            'summary' => [
                'transactions' => (int) ($sales->transactions ?? 0),
                'total_sales' => (float) ($totalSales ?? 0),
                'total_items' => (int) ($items ?? 0),
            ],
            'per_day' => $perDay,
        ]);
    }

    public function stock()
    {
        $items = Sparepart::query()
            ->select('id', 'sku', 'name', 'stock', 'min_stock', 'is_active')
            ->orderBy('name')
            ->get()
            ->map(function (Sparepart $sparepart) {
                return [
                    'id' => $sparepart->id,
                    'sku' => $sparepart->sku,
                    'name' => $sparepart->name,
                    'stock' => $sparepart->stock,
                    'min_stock' => $sparepart->min_stock,
                    'is_low' => $sparepart->stock <= $sparepart->min_stock,
                    'is_active' => $sparepart->is_active,
                ];
            });

        return response()->json([
            'total_items' => $items->count(),
            'low_stock' => $items->where('is_low', true)->count(),
            'items' => $items,
        ]);
    }

    public function profit(Request $request)
    {
        $range = $this->dateRange($request);

        $profit = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($range['from'], fn ($q) => $q->whereDate('sales.sold_at', '>=', $range['from']))
            ->when($range['to'], fn ($q) => $q->whereDate('sales.sold_at', '<=', $range['to']))
            ->selectRaw('SUM((sale_items.price - sale_items.cost) * sale_items.qty) as gross_profit')
            ->value('gross_profit');

        $totalSales = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($range['from'], fn ($q) => $q->whereDate('sales.sold_at', '>=', $range['from']))
            ->when($range['to'], fn ($q) => $q->whereDate('sales.sold_at', '<=', $range['to']))
            ->selectRaw('SUM(sale_items.qty * sale_items.price) as total_sales')
            ->value('total_sales');

        return response()->json([
            'range' => $range,
            'gross_profit' => (float) ($profit ?? 0),
            'total_sales' => (float) $totalSales,
        ]);
    }

    private function dateRange(Request $request): array
    {
        return [
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];
    }
}
