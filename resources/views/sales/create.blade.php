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
                                            <option value="{{ $sparepart->id }}" data-price="{{ $sparepart->price_sell }}">{{ $sparepart->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][qty]" class="form-control" value="1" min="1" required>
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
                    <button class="btn btn-primary" type="submit">Simpan</button>
                    <a class="btn btn-outline-secondary" href="{{ route('sales.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const tableBody = document.querySelector('#items-table tbody');
    const addButton = document.getElementById('add-item');
    const canEditPrice = {{ $canEditPrice ? 'true' : 'false' }};
    let rowIndex = 1;

    const updatePriceFromSelection = (selectEl) => {
        const row = selectEl.closest('tr');
        const priceInput = row.querySelector('input[name*="[price]"]');
        if (!priceInput) {
            return;
        }

        const selected = selectEl.options[selectEl.selectedIndex];
        const price = selected?.dataset?.price ?? '0';
        priceInput.value = price;
    };

    addButton.addEventListener('click', () => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="items[${rowIndex}][sparepart_id]" class="form-select" required>
                    <option value="">-</option>
                    @foreach ($spareparts as $sparepart)
                        <option value="{{ $sparepart->id }}" data-price="{{ $sparepart->price_sell }}">{{ $sparepart->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][qty]" class="form-control" value="1" min="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][price]" class="form-control" value="0" min="0" required ${canEditPrice ? '' : 'readonly'}>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">Hapus</button>
            </td>
        `;
        tableBody.appendChild(row);
        rowIndex++;
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
            updatePriceFromSelection(event.target);
        }
    });
</script>
@endsection
