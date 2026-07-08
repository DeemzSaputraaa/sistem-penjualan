@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Customer</div>
                <p class="page-subtitle">Kelola data pelanggan untuk transaksi.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('customers.create') }}">Tambah</a>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->email }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('customers.edit', $customer) }}">Edit</a>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Hapus customer ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
