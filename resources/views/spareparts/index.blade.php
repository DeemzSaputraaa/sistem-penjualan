@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Sparepart</div>
                <p class="page-subtitle">Kelola daftar sparepart, stok, dan harga jual.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('spareparts.create') }}">Tambah</a>
        </div>

        <form class="row g-3 align-items-end mb-3" method="GET" action="{{ route('spareparts.index') }}">
            <div class="col-12 col-md-5">
                <label class="form-label">Cari</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama atau SKU">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">Terapkan</button>
                <a class="btn btn-outline-secondary flex-grow-1" href="{{ route('spareparts.index') }}">Reset</a>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga Jual</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($spareparts as $sparepart)
                            <tr>
                                <td>{{ $sparepart->sku }}</td>
                                <td>{{ $sparepart->name }}</td>
                                <td>{{ $sparepart->category?->name }}</td>
                                <td>{{ $sparepart->stock }}</td>
                                <td>Rp {{ number_format($sparepart->price_sell, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $sparepart->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $sparepart->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('spareparts.edit', $sparepart) }}">Edit</a>
                                    <form action="{{ route('spareparts.destroy', $sparepart) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Hapus sparepart ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $spareparts->links() }}
        </div>
    </div>
</div>
@endsection
