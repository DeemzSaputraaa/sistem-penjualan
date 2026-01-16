@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Tambah User</div>
                <p class="page-subtitle">Buat akun baru dan tetapkan aksesnya.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Kembali</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    @php($requirePassword = true)
                    @php($selectedRoles = [])
                    @php($selectedPermissions = [])
                    @include('users._form')
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
