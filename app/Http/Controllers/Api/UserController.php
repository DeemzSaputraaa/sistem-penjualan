<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::query()
            ->with('roles', 'permissions')
            ->orderBy('name')
            ->paginate(20);
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

        if (! empty($data['role_ids'])) {
            $user->roles()->sync($data['role_ids']);
        }

        if (! empty($data['permission_ids'])) {
            $user->permissions()->sync($data['permission_ids']);
        }

        return response()->json($user->load('roles', 'permissions'), 201);
    }

    public function show(User $user)
    {
        return $user->load('roles', 'permissions');
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

        if (array_key_exists('role_ids', $data)) {
            $user->roles()->sync($data['role_ids'] ?? []);
        }

        if (array_key_exists('permission_ids', $data)) {
            $user->permissions()->sync($data['permission_ids'] ?? []);
        }

        return $user->load('roles', 'permissions');
    }

    public function destroy(User $user)
    {
        $user->roles()->detach();
        $user->permissions()->detach();
        $user->delete();

        return response()->noContent();
    }
}
