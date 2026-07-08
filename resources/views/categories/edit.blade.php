@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div class="page-title">Edit Kategori</div>
                <p class="page-subtitle">Perbarui detail kategori.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('categories.index') }}">Kembali</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('categories.update', $category) }}">
                    @csrf
                    @method('PUT')
                    @include('categories._form')
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Update</button>
                        <a class="btn btn-outline-secondary" href="{{ route('categories.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
