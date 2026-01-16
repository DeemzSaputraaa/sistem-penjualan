@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Laporan Penjualan</h4>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Dari</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total Transaksi</div>
                    <div class="fs-4 fw-bold">{{ $summary['transactions'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total Item Terjual</div>
                    <div class="fs-4 fw-bold">{{ $summary['total_items'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total Penjualan</div>
                    <div class="fs-4 fw-bold">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Transaksi</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($perDay as $row)
                        <tr>
                            <td>{{ $row->date }}</td>
                            <td>{{ $row->transactions }}</td>
                            <td>Rp {{ number_format($row->total_sales, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
