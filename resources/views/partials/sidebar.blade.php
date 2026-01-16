@php($user = Auth::user())

<div class="sidebar-nav">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">SP</div>
        <div class="sidebar-brand-text">Sistem Penjualan</div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Dashboards</div>
        <a class="sidebar-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">
            <span class="sidebar-icon">DB</span>
            Dashboard
        </a>
    </div>

    @if ($user?->hasAnyPermission(['manage-categories', 'manage-spareparts', 'manage-suppliers', 'manage-customers']))
        <div class="sidebar-section">
            <div class="sidebar-section-title">Master Data</div>
            @if ($user?->hasPermission('manage-categories'))
                <a class="sidebar-link {{ request()->routeIs('categories.*') ? 'is-active' : '' }}" href="{{ route('categories.index') }}">
                    <span class="sidebar-icon">CT</span>
                    Kategori
                </a>
            @endif
            @if ($user?->hasPermission('manage-spareparts'))
                <a class="sidebar-link {{ request()->routeIs('spareparts.*') ? 'is-active' : '' }}" href="{{ route('spareparts.index') }}">
                    <span class="sidebar-icon">SP</span>
                    Sparepart
                </a>
            @endif
            @if ($user?->hasPermission('manage-suppliers'))
                <a class="sidebar-link {{ request()->routeIs('suppliers.*') ? 'is-active' : '' }}" href="{{ route('suppliers.index') }}">
                    <span class="sidebar-icon">SU</span>
                    Supplier
                </a>
            @endif
            @if ($user?->hasPermission('manage-customers'))
                <a class="sidebar-link {{ request()->routeIs('customers.*') ? 'is-active' : '' }}" href="{{ route('customers.index') }}">
                    <span class="sidebar-icon">CS</span>
                    Customer
                </a>
            @endif
        </div>
    @endif

    @if ($user?->hasPermission('manage-purchases'))
        <div class="sidebar-section">
            <div class="sidebar-section-title">Pembelian</div>
            <a class="sidebar-link {{ request()->routeIs('purchases.index') ? 'is-active' : '' }}" href="{{ route('purchases.index') }}">
                <span class="sidebar-icon">PO</span>
                Data Pembelian
            </a>
            <a class="sidebar-link {{ request()->routeIs('purchases.create') ? 'is-active' : '' }}" href="{{ route('purchases.create') }}">
                <span class="sidebar-icon">AD</span>
                Tambah Pembelian
            </a>
        </div>
    @endif

    @if ($user?->hasPermission('manage-sales'))
        <div class="sidebar-section">
            <div class="sidebar-section-title">Penjualan</div>
            <a class="sidebar-link {{ request()->routeIs('sales.index') ? 'is-active' : '' }}" href="{{ route('sales.index') }}">
                <span class="sidebar-icon">SL</span>
                Data Penjualan
            </a>
            @if ($user?->hasRole('kasir'))
                <a class="sidebar-link {{ request()->routeIs('sales.create') ? 'is-active' : '' }}" href="{{ route('sales.create') }}">
                    <span class="sidebar-icon">TR</span>
                    Transaksi Penjualan
                </a>
            @endif
        </div>
    @endif

    @if ($user?->hasPermission('manage-stock'))
        <div class="sidebar-section">
            <div class="sidebar-section-title">Stok</div>
            <a class="sidebar-link {{ request()->routeIs('stock-adjustments.*') ? 'is-active' : '' }}" href="{{ route('stock-adjustments.create') }}">
                <span class="sidebar-icon">ST</span>
                Penyesuaian Stok
            </a>
        </div>
    @endif

    @if ($user?->hasPermission('manage-users'))
        <div class="sidebar-section">
            <div class="sidebar-section-title">User</div>
            <a class="sidebar-link {{ request()->routeIs('users.*') ? 'is-active' : '' }}" href="{{ route('users.index') }}">
                <span class="sidebar-icon">US</span>
                Manajemen User
            </a>
        </div>
    @endif

    @if ($user?->hasPermission('view-reports'))
        <div class="sidebar-section">
            <div class="sidebar-section-title">Laporan</div>
            <a class="sidebar-link {{ request()->routeIs('reports.sales') ? 'is-active' : '' }}" href="{{ route('reports.sales') }}">
                <span class="sidebar-icon">RP</span>
                Laporan Penjualan
            </a>
            <a class="sidebar-link {{ request()->routeIs('reports.stock') ? 'is-active' : '' }}" href="{{ route('reports.stock') }}">
                <span class="sidebar-icon">ST</span>
                Laporan Stok
            </a>
            <a class="sidebar-link {{ request()->routeIs('reports.profit') ? 'is-active' : '' }}" href="{{ route('reports.profit') }}">
                <span class="sidebar-icon">PR</span>
                Laporan Laba/Rugi
            </a>
        </div>
    @endif
</div>
