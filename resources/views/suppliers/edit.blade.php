@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Edit Supplier</h4>
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
@endsection
