@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Sparepart</h4>
        <a class="btn btn-primary" href="{{ route('spareparts.create') }}">Tambah</a>
    </div>

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
@endsection
