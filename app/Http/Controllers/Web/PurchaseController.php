<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Sparepart;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::query()
            ->with('supplier')
            ->latest()
            ->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $this->authorize('create', Purchase::class);

        $suppliers = Supplier::orderBy('name')->get();
        $spareparts = Sparepart::orderBy('name')->get();
        $purchaseNo = 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));

        return view('purchases.create', compact('suppliers', 'spareparts', 'purchaseNo'));
    }

    public function store(Request $request, PurchaseService $purchaseService)
    {
        $this->authorize('create', Purchase::class);

        $data = $request->validate([
            'purchase_no' => ['required', 'string', 'max:50', 'unique:purchases,purchase_no'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'status' => ['required', 'string', 'in:draft,ordered'],
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
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'Pembelian berhasil dibuat.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('items.sparepart', 'supplier', 'user');

        return view('purchases.show', compact('purchase'));
    }

    public function order(Purchase $purchase, PurchaseService $purchaseService, Request $request)
    {
        $this->authorize('order', $purchase);

        try {
            $purchaseService->order($purchase, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'Pembelian dikonfirmasi sebagai Ordered.');
    }

    public function receive(Purchase $purchase, PurchaseService $purchaseService, Request $request)
    {
        $this->authorize('receive', $purchase);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:purchase_items,id'],
            'items.*.received_qty' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $purchaseService->receive($purchase, $data, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'Pembelian diproses.');
    }
}
