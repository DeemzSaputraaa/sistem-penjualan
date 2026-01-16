<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Sparepart;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::query()
            ->with('customer')
            ->latest()
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $spareparts = Sparepart::orderBy('name')->get();
        $invoiceNo = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));

        return view('sales.create', compact('customers', 'spareparts', 'invoiceNo'));
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
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('sales.show', $sale)->with('status', 'Penjualan berhasil dibuat.');
    }

    public function show(Sale $sale)
    {
        $sale->load('items.sparepart', 'customer', 'user');

        return view('sales.show', compact('sale'));
    }
}
