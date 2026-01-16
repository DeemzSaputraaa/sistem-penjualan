@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Penyesuaian Stok</div>
                <p class="page-subtitle">Catat perubahan stok dengan alasan yang jelas.</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('stock-adjustments.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Cari SKU / Nama</label>
                        <input type="text" id="stock-search" class="form-control" placeholder="Ketik SKU atau nama sparepart" list="stock-list">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sparepart</label>
                        <select name="sparepart_id" class="form-select" id="sparepart-select" required>
                            <option value="">-</option>
                            @foreach ($spareparts as $sparepart)
                                <option value="{{ $sparepart->id }}" data-stock="{{ $sparepart->stock }}">
                                    {{ $sparepart->name }} ({{ $sparepart->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('sparepart_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Saat Ini</label>
                        <input type="text" id="current-stock" class="form-control" value="-" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Qty (+/-)</label>
                        <input type="number" name="qty" class="form-control @error('qty') is-invalid @enderror" required>
                        <div class="form-text">Gunakan angka negatif untuk mengurangi stok. Maksimal penyesuaian ±50 unit.</div>
                        @error('qty')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" required></textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </form>
            </div>
        </div>

        <datalist id="stock-list">
            @foreach ($spareparts as $sparepart)
                <option value="{{ $sparepart->sku }} - {{ $sparepart->name }}"></option>
            @endforeach
        </datalist>
    </div>
</div>

<script>
    const stockSearch = document.getElementById('stock-search');
    const sparepartSelect = document.getElementById('sparepart-select');
    const currentStock = document.getElementById('current-stock');

    const updateCurrentStock = () => {
        const selected = sparepartSelect.options[sparepartSelect.selectedIndex];
        const stock = selected?.dataset?.stock;
        currentStock.value = stock ?? '-';
    };

    const matchSparepart = (value) => {
        const raw = value.trim();
        if (!raw) {
            return null;
        }

        const skuCandidate = raw.includes(' - ') ? raw.split(' - ')[0].trim().toLowerCase() : raw.toLowerCase();
        return Array.from(sparepartSelect.options).find((option) => {
            return option.textContent?.toLowerCase().includes(skuCandidate) ||
                option.value === skuCandidate;
        });
    };

    stockSearch.addEventListener('change', () => {
        const match = matchSparepart(stockSearch.value);
        if (match) {
            sparepartSelect.value = match.value;
            updateCurrentStock();
        }
    });

    sparepartSelect.addEventListener('change', updateCurrentStock);
    updateCurrentStock();
</script>
@endsection
