@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Tambah Customer</div>
                <p class="page-subtitle">Lengkapi data customer baru.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('customers.index') }}">Kembali</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf
                    @include('customers._form')
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <a class="btn btn-outline-secondary" href="{{ route('customers.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
