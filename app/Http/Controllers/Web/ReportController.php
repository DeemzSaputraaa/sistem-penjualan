<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Sparepart;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function exportSales(Request $request, AuditLogService $auditLog): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $perDay = Sale::query()
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->when($from, fn ($q) => $q->whereDate('sales.sold_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sales.sold_at', '<=', $to))
            ->selectRaw('DATE(sales.sold_at) as date, COUNT(DISTINCT sales.id) as transactions, SUM(sale_items.qty * sale_items.price) as total_sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $auditLog->log('report.sales.export', null, $request->user(), $request, null, [
            'from' => $from,
            'to' => $to,
        ]);

        $filename = 'sales-report-' . now()->format('YmdHis') . '.csv';

        return $this->csvResponse($filename, function ($handle) use ($perDay) {
            fputcsv($handle, ['Tanggal', 'Transaksi', 'Total Penjualan']);
            foreach ($perDay as $row) {
                fputcsv($handle, [
                    $row->date,
                    $row->transactions,
                    number_format((float) $row->total_sales, 2, '.', ''),
                ]);
            }
        });
    }

    public function exportStock(Request $request, AuditLogService $auditLog): StreamedResponse
    {
        $items = Sparepart::query()
            ->select('sku', 'name', 'stock', 'min_stock')
            ->orderBy('name')
            ->get();

        $auditLog->log('report.stock.export', null, $request->user(), $request, null, null);

        $filename = 'stock-report-' . now()->format('YmdHis') . '.csv';

        return $this->csvResponse($filename, function ($handle) use ($items) {
            fputcsv($handle, ['SKU', 'Nama', 'Stok', 'Min Stok', 'Low Stock']);
            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->sku,
                    $item->name,
                    $item->stock,
                    $item->min_stock,
                    $item->stock <= $item->min_stock ? 'YES' : 'NO',
                ]);
            }
        });
    }

    public function exportProfit(Request $request, AuditLogService $auditLog): StreamedResponse
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

        $auditLog->log('report.profit.export', null, $request->user(), $request, null, [
            'from' => $from,
            'to' => $to,
        ]);

        $filename = 'profit-report-' . now()->format('YmdHis') . '.csv';

        return $this->csvResponse($filename, function ($handle) use ($from, $to, $gross, $totalSales) {
            fputcsv($handle, ['Range From', 'Range To', 'Total Sales', 'Gross Profit']);
            fputcsv($handle, [
                $from ?: '-',
                $to ?: '-',
                number_format((float) $totalSales, 2, '.', ''),
                number_format((float) $gross, 2, '.', ''),
            ]);
        });
    }

    private function range(Request $request): array
    {
        return [
            $request->query('from'),
            $request->query('to'),
        ];
    }

    private function csvResponse(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer) {
            $handle = fopen('php://output', 'w');
            $writer($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
