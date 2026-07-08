@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Laporan Stok</div>
                <p class="page-subtitle">Pantau stok dan item yang mendekati minimum.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('reports.stock.export') }}">Export CSV</a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="text-muted">Stok Minimum</div>
                <div class="fs-4 fw-bold">{{ $lowStock }} item</div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama</th>
                            <th>Stok</th>
                            <th>Min Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->stock }}</td>
                                <td>{{ $item->min_stock }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
