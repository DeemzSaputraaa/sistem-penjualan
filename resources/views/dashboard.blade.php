@extends('layouts.app')

@section('body-class', 'dashboard-page')
@section('navbar-class', 'dashboard-navbar')
@section('main-class', 'dashboard-main')

@push('styles')
@vite('resources/css/dashboard-superadmin.css')
@endpush

@section('content')
<div class="dashboard-shell">
    <div class="dashboard-header">
        <div>
            <div class="dashboard-eyebrow">Dashboard Super Admin</div>
            <h1 class="dashboard-title">Ringkasan Sistem Penjualan</h1>
            <p class="dashboard-subtitle">Pantau stok, transaksi, dan performa harian secara real-time.</p>
        </div>
        <div class="dashboard-meta">
            <div class="meta-pill">Hari ini: {{ now()->format('d M Y') }}</div>
            <div class="meta-pill">Role: {{ auth()->user()->roles->pluck('label')->first() ?? 'User' }}</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
            <div class="stat-card stat-card--violet">
                <div class="stat-icon">SP</div>
                <div class="stat-label">Total Sparepart</div>
                <div class="stat-value">{{ $stats['spareparts'] }}</div>
                <a href="{{ route('spareparts.index') }}" class="btn btn-sm btn-light">Lihat Detail</a>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
            <div class="stat-card stat-card--amber">
                <div class="stat-icon">ST</div>
                <div class="stat-label">Stok Minimum</div>
                <div class="stat-value">{{ $stats['low_stock'] }}</div>
                <a href="{{ route('reports.stock') }}" class="btn btn-sm btn-light">Lihat Detail</a>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
            <div class="stat-card stat-card--teal">
                <div class="stat-icon">PJ</div>
                <div class="stat-label">Penjualan Hari Ini</div>
                <div class="stat-value">Rp {{ number_format($stats['sales_today'], 0, ',', '.') }}</div>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-light">Lihat Detail</a>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
            <div class="stat-card stat-card--rose">
                <div class="stat-icon">PB</div>
                <div class="stat-label">Pembelian Hari Ini</div>
                <div class="stat-value">Rp {{ number_format($stats['purchases_today'], 0, ',', '.') }}</div>
                <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-light">Lihat Detail</a>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
            <div class="stat-card stat-card--mint">
                <div class="stat-icon">US</div>
                <div class="stat-label">Total User</div>
                <div class="stat-value">{{ $stats['users'] }}</div>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">Lihat Detail</a>
            </div>
        </div>
    </div>
</div>
@endsection
