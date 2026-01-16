@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Tambah Customer</h4>
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
@endsection
