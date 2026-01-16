@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Pembelian</h4>
        <a class="btn btn-outline-secondary" href="{{ route('purchases.index') }}">Kembali</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2"><strong>No:</strong> {{ $purchase->purchase_no }}</div>
                <div class="col-md-4 mb-2"><strong>Supplier:</strong> {{ $purchase->supplier?->name }}</div>
                <div class="col-md-4 mb-2"><strong>Status:</strong> {{ strtoupper($purchase->status) }}</div>
                <div class="col-md-4 mb-2"><strong>Tanggal:</strong> {{ optional($purchase->purchased_at)->format('d/m/Y') }}</div>
                <div class="col-md-4 mb-2"><strong>Total:</strong> Rp {{ number_format($purchase->total, 0, ',', '.') }}</div>
                <div class="col-md-4 mb-2"><strong>Input:</strong> {{ $purchase->user?->name }}</div>
                <div class="col-12 mb-2"><strong>Catatan:</strong> {{ $purchase->notes }}</div>
            </div>

            @if ($purchase->status !== 'received')
                <form method="POST" action="{{ route('purchases.receive', $purchase) }}" onsubmit="return confirm('Terima pembelian dan tambah stok?')">
                    @csrf
                    <button class="btn btn-success">Terima Pembelian</button>
                </form>
            @endif
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
                    @foreach ($purchase->items as $item)
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
