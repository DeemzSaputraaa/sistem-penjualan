@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <h4 class="mb-3">Penyesuaian Stok</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('stock-adjustments.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Sparepart</label>
                    <select name="sparepart_id" class="form-select" required>
                        <option value="">-</option>
                        @foreach ($spareparts as $sparepart)
                            <option value="{{ $sparepart->id }}">{{ $sparepart->name }} (Stok: {{ $sparepart->stock }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Qty (+/-)</label>
                    <input type="number" name="qty" class="form-control" required>
                    <div class="form-text">Gunakan angka negatif untuk mengurangi stok.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" required></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
