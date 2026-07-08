@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Edit Supplier</div>
                <p class="page-subtitle">Perbarui data supplier.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('suppliers.index') }}">Kembali</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                    @csrf
                    @method('PUT')
                    @include('suppliers._form')
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Update</button>
                        <a class="btn btn-outline-secondary" href="{{ route('suppliers.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
