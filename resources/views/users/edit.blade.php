@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Edit User</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @php($requirePassword = false)
                @php($selectedRoles = $user->roles->pluck('id')->all())
                @php($selectedPermissions = $user->permissions->pluck('id')->all())
                @include('users._form')
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
