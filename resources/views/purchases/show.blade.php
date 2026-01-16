@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Detail Pembelian</div>
                <p class="page-subtitle">Pantau status dan penerimaan barang.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('purchases.index') }}">Kembali</a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2"><strong>No:</strong> {{ $purchase->purchase_no }}</div>
                    <div class="col-md-4 mb-2"><strong>Supplier:</strong> {{ $purchase->supplier?->name }}</div>
                    <div class="col-md-4 mb-2">
                        <strong>Status:</strong>
                        @php($status = $purchase->status)
                        <span class="badge {{ $status === 'received' ? 'bg-success' : ($status === 'ordered' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ strtoupper($status) }}
                        </span>
                    </div>
                    <div class="col-md-4 mb-2"><strong>Tanggal:</strong> {{ optional($purchase->purchased_at)->format('d/m/Y') }}</div>
                    <div class="col-md-4 mb-2"><strong>Total:</strong> Rp {{ number_format($purchase->total, 0, ',', '.') }}</div>
                    <div class="col-md-4 mb-2"><strong>Input:</strong> {{ $purchase->user?->name }}</div>
                    <div class="col-12 mb-2"><strong>Catatan:</strong> {{ $purchase->notes }}</div>
                </div>

                @if ($purchase->status === 'draft')
                    <form method="POST" action="{{ route('purchases.order', $purchase) }}" class="mt-2" onsubmit="return confirm('Konfirmasi pembelian menjadi Ordered?')">
                        @csrf
                        <button class="btn btn-primary">Konfirmasi Ordered</button>
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
                            <th>Diterima</th>
                            <th>Sisa</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchase->items as $item)
                            @php($remaining = max(0, $item->qty - $item->received_qty))
                            <tr>
                                <td>{{ $item->sparepart?->name }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ $item->received_qty }}</td>
                                <td>{{ $remaining }}</td>
                                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($purchase->status === 'ordered')
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('purchases.receive', $purchase) }}" onsubmit="return confirm('Terima pembelian dan tambah stok?')">
                        @csrf
                        <h5 class="mb-3">Penerimaan Barang</h5>
                        <div class="row g-3">
                            @foreach ($purchase->items as $index => $item)
                                @php($remaining = max(0, $item->qty - $item->received_qty))
                                <div class="col-12 col-md-6">
                                    <label class="form-label">{{ $item->sparepart?->name }} ({{ $item->sparepart?->sku }})</label>
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    <input type="number" name="items[{{ $index }}][received_qty]" class="form-control" value="{{ $remaining }}" min="0" max="{{ $remaining }}">
                                    <div class="form-text">Sisa: {{ $remaining }}</div>
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-success mt-3">Proses Penerimaan</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
