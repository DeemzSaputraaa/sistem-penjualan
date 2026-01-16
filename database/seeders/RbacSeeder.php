<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super-admin', 'label' => 'Super Admin'],
            ['name' => 'admin', 'label' => 'Admin'],
            ['name' => 'kasir', 'label' => 'Kasir'],
            ['name' => 'gudang', 'label' => 'Gudang'],
            ['name' => 'purchasing', 'label' => 'Purchasing'],
            ['name' => 'owner', 'label' => 'Owner'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], ['label' => $role['label']]);
        }

        $permissions = [
            'manage-master',
            'manage-sales',
            'manage-purchases',
            'manage-stock',
            'view-reports',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['label' => ucwords(str_replace('-', ' ', $permission))]);
        }

        $rolePermissions = [
            'super-admin' => $permissions,
            'admin' => ['manage-master', 'manage-sales', 'manage-purchases', 'manage-stock'],
            'kasir' => ['manage-sales'],
            'gudang' => ['manage-stock'],
            'purchasing' => ['manage-purchases'],
            'owner' => ['view-reports'],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();
            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
            $role?->permissions()->sync($permissionIds);
        }

        $defaultPassword = env('DEFAULT_USER_PASSWORD', 'Password123!');

        $users = [
            [
                'role' => 'super-admin',
                'name' => env('SUPERADMIN_NAME', 'Super Admin'),
                'email' => env('SUPERADMIN_EMAIL', 'superadmin@example.com'),
                'password' => env('SUPERADMIN_PASSWORD', $defaultPassword),
            ],
            [
                'role' => 'admin',
                'name' => env('ADMIN_NAME', 'Admin'),
                'email' => env('ADMIN_EMAIL', 'admin@example.com'),
                'password' => env('ADMIN_PASSWORD', $defaultPassword),
            ],
            [
                'role' => 'kasir',
                'name' => env('KASIR_NAME', 'Kasir'),
                'email' => env('KASIR_EMAIL', 'kasir@example.com'),
                'password' => env('KASIR_PASSWORD', $defaultPassword),
            ],
            [
                'role' => 'gudang',
                'name' => env('GUDANG_NAME', 'Gudang'),
                'email' => env('GUDANG_EMAIL', 'gudang@example.com'),
                'password' => env('GUDANG_PASSWORD', $defaultPassword),
            ],
            [
                'role' => 'purchasing',
                'name' => env('PURCHASING_NAME', 'Purchasing'),
                'email' => env('PURCHASING_EMAIL', 'purchasing@example.com'),
                'password' => env('PURCHASING_PASSWORD', $defaultPassword),
            ],
            [
                'role' => 'owner',
                'name' => env('OWNER_NAME', 'Owner'),
                'email' => env('OWNER_EMAIL', 'owner@example.com'),
                'password' => env('OWNER_PASSWORD', $defaultPassword),
            ],
        ];

        foreach ($users as $entry) {
            $role = Role::where('name', $entry['role'])->first();
            if (! $role) {
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $entry['email']],
                [
                    'name' => $entry['name'],
                    'password' => Hash::make($entry['password']),
                ]
            );

            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
