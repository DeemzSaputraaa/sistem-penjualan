@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Pembelian</h4>
        <a class="btn btn-primary" href="{{ route('purchases.create') }}">Tambah</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No Pembelian</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_no }}</td>
                            <td>{{ $purchase->supplier?->name }}</td>
                            <td>
                                <span class="badge {{ $purchase->status === 'received' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ strtoupper($purchase->status) }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                            <td>{{ optional($purchase->purchased_at)->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('purchases.show', $purchase) }}">Detail</a>
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
        {{ $purchases->links() }}
    </div>
</div>
@endsection
