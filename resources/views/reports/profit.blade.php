@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Laporan Laba/Rugi</div>
                <p class="page-subtitle">Ringkasan laba kotor berdasarkan transaksi.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('reports.profit.export', request()->query()) }}">Export CSV</a>
        </div>

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
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-primary" type="submit">Filter</button>
                        <a class="btn btn-outline-secondary" href="{{ route('reports.profit') }}">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Total Penjualan</div>
                        <div class="fs-4 fw-bold">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Laba Kotor</div>
                        <div class="fs-4 fw-bold">Rp {{ number_format($grossProfit, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
