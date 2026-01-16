@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Penjualan</h4>
        <a class="btn btn-primary" href="{{ route('sales.create') }}">Transaksi</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No Invoice</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Bayar</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->customer?->name }}</td>
                            <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($sale->paid, 0, ',', '.') }}</td>
                            <td>{{ optional($sale->sold_at)->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('sales.show', $sale) }}">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $sales->links() }}
    </div>
</div>
@endsection
