@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Laporan Stok</h4>

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
@endsection
