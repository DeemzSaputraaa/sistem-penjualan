@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Tambah Sparepart</div>
                <p class="page-subtitle">Isi detail sparepart untuk stok dan transaksi.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('spareparts.index') }}">Kembali</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('spareparts.store') }}">
                    @csrf
                    @include('spareparts._form')
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <a class="btn btn-outline-secondary" href="{{ route('spareparts.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
