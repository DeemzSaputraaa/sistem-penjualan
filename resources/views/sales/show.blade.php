@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Penjualan</h4>
        <a class="btn btn-outline-secondary" href="{{ route('sales.index') }}">Kembali</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2"><strong>No:</strong> {{ $sale->invoice_no }}</div>
                <div class="col-md-4 mb-2"><strong>Customer:</strong> {{ $sale->customer?->name }}</div>
                <div class="col-md-4 mb-2"><strong>Tanggal:</strong> {{ optional($sale->sold_at)->format('d/m/Y') }}</div>
                <div class="col-md-4 mb-2"><strong>Total:</strong> Rp {{ number_format($sale->total, 0, ',', '.') }}</div>
                <div class="col-md-4 mb-2"><strong>Dibayar:</strong> Rp {{ number_format($sale->paid, 0, ',', '.') }}</div>
                <div class="col-md-4 mb-2"><strong>Kembalian:</strong> Rp {{ number_format($sale->change, 0, ',', '.') }}</div>
                <div class="col-md-4 mb-2"><strong>Kasir:</strong> {{ $sale->user?->name }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Sparepart</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->sparepart?->name }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
