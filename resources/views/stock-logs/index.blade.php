@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Histori Stok</div>
                <p class="page-subtitle">Pantau perubahan stok dan alasan penyesuaian.</p>
            </div>
        </div>

        <form class="row g-3 align-items-end mb-3" method="GET" action="{{ route('stock-logs.index') }}">
            <div class="col-12 col-md-6">
                <label class="form-label">Cari</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="SKU atau nama sparepart">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Tipe</label>
                <select name="type" class="form-select">
                    <option value="">Semua</option>
                    <option value="in" @selected(($filters['type'] ?? '') === 'in')>Masuk</option>
                    <option value="out" @selected(($filters['type'] ?? '') === 'out')>Keluar</option>
                    <option value="adjust" @selected(($filters['type'] ?? '') === 'adjust')>Penyesuaian</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">Terapkan</button>
                <a class="btn btn-outline-secondary flex-grow-1" href="{{ route('stock-logs.index') }}">Reset</a>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Sparepart</th>
                            <th>Tipe</th>
                            <th>Qty</th>
                            <th>Stok Sebelum</th>
                            <th>Stok Sesudah</th>
                            <th>User</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td>{{ $log->sparepart?->name }} ({{ $log->sparepart?->sku }})</td>
                                <td>{{ strtoupper($log->type) }}</td>
                                <td>{{ $log->qty }}</td>
                                <td>{{ $log->before_stock }}</td>
                                <td>{{ $log->after_stock }}</td>
                                <td>{{ $log->user?->name ?? '-' }}</td>
                                <td>{{ $log->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
