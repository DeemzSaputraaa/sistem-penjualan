<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Sparepart;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request, AuditLogService $auditLog)
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::query()
            ->when($from, fn ($q) => $q->whereDate('sold_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sold_at', '<=', $to))
            ->selectRaw('COUNT(*) as transactions')
            ->first();

        $totalSales = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($from, fn ($q) => $q->whereDate('sales.sold_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sales.sold_at', '<=', $to))
            ->selectRaw('SUM(sale_items.qty * sale_items.price) as total_sales')
            ->value('total_sales');

        $items = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($from, fn ($q) => $q->whereDate('sales.sold_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sales.sold_at', '<=', $to))
            ->selectRaw('SUM(sale_items.qty) as total_items')
            ->value('total_items');

        $perDay = Sale::query()
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->when($from, fn ($q) => $q->whereDate('sales.sold_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sales.sold_at', '<=', $to))
            ->selectRaw('DATE(sales.sold_at) as date, COUNT(DISTINCT sales.id) as transactions, SUM(sale_items.qty * sale_items.price) as total_sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $auditLog->log('report.sales.view', null, $request->user(), $request, null, [
            'from' => $from,
            'to' => $to,
        ]);

        return view('reports.sales', [
            'from' => $from,
            'to' => $to,
            'summary' => [
                'transactions' => (int) ($sales->transactions ?? 0),
                'total_sales' => (float) ($totalSales ?? 0),
                'total_items' => (int) ($items ?? 0),
            ],
            'perDay' => $perDay,
        ]);
    }

    public function stock(Request $request, AuditLogService $auditLog)
    {
        $items = Sparepart::query()
            ->select('id', 'sku', 'name', 'stock', 'min_stock')
            ->orderBy('name')
            ->get();

        $lowStock = $items->filter(fn ($item) => $item->stock <= $item->min_stock)->count();

        $auditLog->log('report.stock.view', null, $request->user(), $request, null, null);

        return view('reports.stock', [
            'items' => $items,
            'lowStock' => $lowStock,
        ]);
    }

    public function profit(Request $request, AuditLogService $auditLog)
    {
        [$from, $to] = $this->range($request);

        $gross = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($from, fn ($q) => $q->whereDate('sales.sold_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sales.sold_at', '<=', $to))
            ->selectRaw('SUM((sale_items.price - sale_items.cost) * sale_items.qty) as gross_profit')
            ->value('gross_profit');

        $totalSales = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($from, fn ($q) => $q->whereDate('sales.sold_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sales.sold_at', '<=', $to))
            ->selectRaw('SUM(sale_items.qty * sale_items.price) as total_sales')
            ->value('total_sales');

        $auditLog->log('report.profit.view', null, $request->user(), $request, null, [
            'from' => $from,
            'to' => $to,
        ]);

        return view('reports.profit', [
            'from' => $from,
            'to' => $to,
            'grossProfit' => (float) ($gross ?? 0),
            'totalSales' => (float) $totalSales,
        ]);
    }

    private function range(Request $request): array
    {
        return [
            $request->query('from'),
            $request->query('to'),
        ];
    }
}
