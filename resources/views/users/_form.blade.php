<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" @if($requirePassword) required @endif>
        @if (! $requirePassword)
            <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
        @endif
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Role</label>
        <select name="role_ids[]" class="form-select" multiple>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(in_array($role->id, old('role_ids', $selectedRoles ?? [])))>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Permissions</label>
        <div class="row">
            @foreach ($permissions as $permission)
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permission_ids[]"
                               value="{{ $permission->id }}"
                               @checked(in_array($permission->id, old('permission_ids', $selectedPermissions ?? [])))>
                        <label class="form-check-label">{{ $permission->name }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
