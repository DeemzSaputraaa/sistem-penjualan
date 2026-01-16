<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->with('roles', 'permissions')->orderBy('name')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('users.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->roles()->sync($data['role_ids'] ?? []);
        $user->permissions()->sync($data['permission_ids'] ?? []);

        return redirect()->route('users.index')->with('status', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);
        $user->roles()->sync($data['role_ids'] ?? []);
        $user->permissions()->sync($data['permission_ids'] ?? []);

        return redirect()->route('users.index')->with('status', 'User diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->roles()->detach();
        $user->permissions()->detach();
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User dihapus.');
    }
}
