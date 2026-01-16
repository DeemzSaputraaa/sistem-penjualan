@extends('layouts.app')

@section('content')
@php($canEditPrice = auth()->user()?->hasPermission('manage-pricing'))
<div class="container-fluid">
    <h4 class="mb-3">Transaksi Penjualan</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('sales.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">No Invoice</label>
                        <input type="text" name="invoice_no" class="form-control" value="{{ old('invoice_no', $invoiceNo) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select">
                            <option value="">-</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="sold_at" class="form-control" value="{{ old('sold_at', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Dibayar</label>
                        <input type="number" step="0.01" name="paid" class="form-control" value="{{ old('paid', 0) }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row align-items-end g-3">
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Cari SKU / Barcode</label>
                                <input type="text" id="quick-sku" class="form-control" placeholder="Ketik SKU lalu Enter" autocomplete="off" list="spareparts-list">
                                <div class="form-text">Tekan Enter untuk menambahkan item.</div>
                            </div>
                            <div class="col-6 col-lg-2">
                                <label class="form-label">Qty</label>
                                <input type="number" id="quick-qty" class="form-control" value="1" min="1">
                            </div>
                            <div class="col-6 col-lg-4 d-flex gap-2">
                                <button type="button" class="btn btn-primary w-100" id="quick-add">Tambah</button>
                                <button type="button" class="btn btn-outline-secondary w-100" id="quick-clear">Reset</button>
                            </div>
                        </div>
                        <div class="mt-2 text-muted small" id="quick-message"></div>
                    </div>
                </div>

                <datalist id="spareparts-list">
                    @foreach ($spareparts as $sparepart)
                        <option value="{{ $sparepart->sku }} - {{ $sparepart->name }}"></option>
                    @endforeach
                </datalist>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Item Penjualan</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">Tambah Item</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="items-table">
                        <thead>
                            <tr>
                                <th>Sparepart</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="items[0][sparepart_id]" class="form-select" required>
                                        <option value="">-</option>
                                        @foreach ($spareparts as $sparepart)
                                            <option value="{{ $sparepart->id }}" data-price="{{ $sparepart->price_sell }}" data-sku="{{ $sparepart->sku }}" data-stock="{{ $sparepart->stock }}">
                                                {{ $sparepart->name }} ({{ $sparepart->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][qty]" class="form-control" value="1" min="1" required>
                                    <div class="form-text stock-hint"></div>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][price]" class="form-control" value="0" min="0" required @readonly(! $canEditPrice)>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit" id="submit-sale">Simpan</button>
                    <a class="btn btn-outline-secondary" href="{{ route('sales.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@php($sparepartsPayload = $spareparts->map(function ($item) {
    return [
        'id' => $item->id,
        'sku' => $item->sku,
        'name' => $item->name,
        'price_sell' => $item->price_sell,
        'stock' => $item->stock,
    ];
})->values()->all())

<script>
    const tableBody = document.querySelector('#items-table tbody');
    const addButton = document.getElementById('add-item');
    const quickSku = document.getElementById('quick-sku');
    const quickQty = document.getElementById('quick-qty');
    const quickAdd = document.getElementById('quick-add');
    const quickClear = document.getElementById('quick-clear');
    const quickMessage = document.getElementById('quick-message');
    const submitButton = document.getElementById('submit-sale');
    const canEditPrice = {{ $canEditPrice ? 'true' : 'false' }};
    const spareparts = @json($sparepartsPayload);
    let rowIndex = 1;

    const setMessage = (text, tone = 'muted') => {
        quickMessage.className = `mt-2 small text-${tone}`;
        quickMessage.textContent = text;
    };

    const updateRowMeta = (row) => {
        const select = row.querySelector('select[name*="[sparepart_id]"]');
        const qtyInput = row.querySelector('input[name*="[qty]"]');
        const priceInput = row.querySelector('input[name*="[price]"]');
        const stockHint = row.querySelector('.stock-hint');
        if (!select || !qtyInput || !priceInput) {
            return;
        }

        const selected = select.options[select.selectedIndex];
        const price = selected?.dataset?.price ?? '0';
        const stock = selected?.dataset?.stock ?? '';

        priceInput.value = price;
        if (stockHint) {
            stockHint.textContent = stock ? `Stok tersedia: ${stock}` : '';
        }
    };

    const findSparepartByInput = (input) => {
        const raw = input.trim();
        const normalized = raw.toLowerCase();
        if (!normalized) {
            return null;
        }

        const skuCandidate = raw.includes(' - ') ? raw.split(' - ')[0].trim() : raw;
        const skuNormalized = skuCandidate.toLowerCase();

        const exact = spareparts.find((item) =>
            item.sku.toLowerCase() === skuNormalized ||
            item.name.toLowerCase() === normalized
        );
        if (exact) {
            return exact;
        }

        return spareparts.find((item) =>
            item.sku.toLowerCase().startsWith(skuNormalized) ||
            item.name.toLowerCase().includes(normalized)
        );
    };

    const getExistingRowBySparepartId = (sparepartId) => {
        return Array.from(tableBody.querySelectorAll('tr')).find((row) => {
            const select = row.querySelector('select[name*="[sparepart_id]"]');
            return select?.value === String(sparepartId);
        });
    };

    const addRow = (sparepartId, qty = 1) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="items[${rowIndex}][sparepart_id]" class="form-select" required>
                    <option value="">-</option>
                    @foreach ($spareparts as $sparepart)
                        <option value="{{ $sparepart->id }}" data-price="{{ $sparepart->price_sell }}" data-sku="{{ $sparepart->sku }}" data-stock="{{ $sparepart->stock }}">
                            {{ $sparepart->name }} ({{ $sparepart->sku }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][qty]" class="form-control" value="${qty}" min="1" required>
                <div class="form-text stock-hint"></div>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][price]" class="form-control" value="0" min="0" required ${canEditPrice ? '' : 'readonly'}>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">Hapus</button>
            </td>
        `;
        tableBody.appendChild(row);
        const select = row.querySelector('select');
        select.value = String(sparepartId);
        updateRowMeta(row);
        rowIndex++;
    };

    const clampQtyToStock = (qtyInput, stockValue) => {
        if (!stockValue) {
            return;
        }

        const stock = parseInt(stockValue, 10);
        if (!Number.isFinite(stock)) {
            return;
        }

        const qty = parseInt(qtyInput.value || '0', 10);
        if (qty > stock) {
            qtyInput.value = stock;
        }
    };

    const addOrIncreaseItem = (sparepart, qty) => {
        const existingRow = getExistingRowBySparepartId(sparepart.id);
        if (existingRow) {
            const qtyInput = existingRow.querySelector('input[name*="[qty]"]');
            qtyInput.value = Number(qtyInput.value || 0) + qty;
            clampQtyToStock(qtyInput, sparepart.stock);
            updateRowMeta(existingRow);
        } else {
            addRow(sparepart.id, qty);
        }
    };

    const handleQuickAdd = () => {
        const value = quickSku.value.trim();
        const qtyValue = Math.max(1, parseInt(quickQty.value || '1', 10));
        const sparepart = findSparepartByInput(value);
        if (!sparepart) {
            setMessage('SKU atau nama tidak ditemukan.', 'danger');
            return;
        }

        if (sparepart.stock !== null && qtyValue > sparepart.stock) {
            setMessage(`Stok ${sparepart.name} hanya ${sparepart.stock}.`, 'danger');
            return;
        }

        addOrIncreaseItem(sparepart, qtyValue);
        setMessage(`Menambahkan ${sparepart.name}.`, 'success');
        quickSku.value = '';
        quickQty.value = 1;
        quickSku.focus();
    };

    addButton.addEventListener('click', () => addRow('', 1));

    quickAdd.addEventListener('click', handleQuickAdd);
    quickClear.addEventListener('click', () => {
        quickSku.value = '';
        quickQty.value = 1;
        setMessage('');
        quickSku.focus();
    });
    quickSku.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            handleQuickAdd();
        }
    });

    tableBody.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-item')) {
            const rows = tableBody.querySelectorAll('tr');
            if (rows.length > 1) {
                event.target.closest('tr').remove();
            }
        }
    });

    tableBody.addEventListener('change', (event) => {
        if (event.target.matches('select[name*="[sparepart_id]"]')) {
            updateRowMeta(event.target.closest('tr'));
        }
    });

    tableBody.addEventListener('input', (event) => {
        if (event.target.matches('input[name*="[qty]"]')) {
            const row = event.target.closest('tr');
            const select = row.querySelector('select[name*="[sparepart_id]"]');
            const stock = select?.options[select.selectedIndex]?.dataset?.stock;
            clampQtyToStock(event.target, stock);
            updateRowMeta(row);
        }
    });

    document.querySelector('form').addEventListener('submit', () => {
        submitButton.disabled = true;
        submitButton.textContent = 'Menyimpan...';
    });

    updateRowMeta(tableBody.querySelector('tr'));
</script>
@endsection
