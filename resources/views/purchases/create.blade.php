@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Tambah Pembelian</h4>
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
                            <option value="received" @selected(old('status') === 'received')>Received</option>
                            <option value="draft" @selected(old('status') === 'draft')>Draft</option>
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
                                            <option value="{{ $sparepart->id }}">{{ $sparepart->name }}</option>
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

<script>
    const tableBody = document.querySelector('#items-table tbody');
    const addButton = document.getElementById('add-item');
    let rowIndex = 1;

    addButton.addEventListener('click', () => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="items[${rowIndex}][sparepart_id]" class="form-select" required>
                    <option value="">-</option>
                    @foreach ($spareparts as $sparepart)
                        <option value="{{ $sparepart->id }}">{{ $sparepart->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][qty]" class="form-control" value="1" min="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][price]" class="form-control" value="0" min="0" required>
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
</script>
@endsection
