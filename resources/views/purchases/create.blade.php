@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Tambah Pembelian</div>
                <p class="page-subtitle">Catat PO baru dari supplier.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('purchases.index') }}">Kembali</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('purchases.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No Pembelian</label>
                            <input type="text" name="purchase_no" class="form-control" value="{{ old('purchase_no', $purchaseNo) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">-</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                                <option value="ordered" @selected(old('status') === 'ordered')>Ordered</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="purchased_at" class="form-control" value="{{ old('purchased_at', now()->format('Y-m-d')) }}">
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
                                    <label class="form-label">Cari SKU / Nama</label>
                                    <input type="text" id="quick-sku" class="form-control" placeholder="Ketik SKU lalu Enter" list="spareparts-list" autocomplete="off">
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
                        <h5 class="mb-0">Item Pembelian</h5>
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
                                                <option value="{{ $sparepart->id }}" data-price="{{ $sparepart->price_buy }}" data-sku="{{ $sparepart->sku }}">
                                                    {{ $sparepart->name }} ({{ $sparepart->sku }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][qty]" class="form-control" value="1" min="1" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][price]" class="form-control" value="0" min="0" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <a class="btn btn-outline-secondary" href="{{ route('purchases.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php($sparepartsPayload = $spareparts->map(function ($item) {
    return [
        'id' => $item->id,
        'sku' => $item->sku,
        'name' => $item->name,
        'price_buy' => $item->price_buy,
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
    const spareparts = @json($sparepartsPayload);
    let rowIndex = 1;

    const setMessage = (text, tone = 'muted') => {
        quickMessage.className = `mt-2 small text-${tone}`;
        quickMessage.textContent = text;
    };

    const updateRowMeta = (row) => {
        const select = row.querySelector('select[name*="[sparepart_id]"]');
        const priceInput = row.querySelector('input[name*="[price]"]');
        if (!select || !priceInput) {
            return;
        }

        const selected = select.options[select.selectedIndex];
        const price = selected?.dataset?.price ?? '0';
        priceInput.value = price;
    };

    const findSparepartByInput = (input) => {
        const raw = input.trim();
        const normalized = raw.toLowerCase();
        if (!normalized) {
            return null;
        }

        const skuCandidate = raw.includes(' - ') ? raw.split(' - ')[0].trim().toLowerCase() : normalized;
        const exact = spareparts.find((item) =>
            item.sku.toLowerCase() === skuCandidate ||
            item.name.toLowerCase() === normalized
        );
        if (exact) {
            return exact;
        }

        return spareparts.find((item) =>
            item.sku.toLowerCase().startsWith(skuCandidate) ||
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
                        <option value="{{ $sparepart->id }}" data-price="{{ $sparepart->price_buy }}" data-sku="{{ $sparepart->sku }}">
                            {{ $sparepart->name }} ({{ $sparepart->sku }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][qty]" class="form-control" value="${qty}" min="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][price]" class="form-control" value="0" min="0" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">Hapus</button>
            </td>
        `;
        tableBody.appendChild(row);
        const select = row.querySelector('select');
        if (sparepartId) {
            select.value = String(sparepartId);
        }
        updateRowMeta(row);
        rowIndex++;
    };

    const addOrIncreaseItem = (sparepart, qty) => {
        const existingRow = getExistingRowBySparepartId(sparepart.id);
        if (existingRow) {
            const qtyInput = existingRow.querySelector('input[name*="[qty]"]');
            qtyInput.value = Number(qtyInput.value || 0) + qty;
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

    updateRowMeta(tableBody.querySelector('tr'));
</script>
@endsection
