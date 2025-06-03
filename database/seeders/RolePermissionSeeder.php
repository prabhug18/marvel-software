<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Check if roles exist before creating
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $editorRole = Role::firstOrCreate(['name' => 'Editor']);

        // Assign all permissions to Admin
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        // Assign specific permissions to Editor
        $editorPermissions = Permission::whereIn('name', [
            'brand-list', 'brand-create', 'brand-edit',
            'category-list', 'category-create', 'category-edit',
        ])->get();
        $editorRole->syncPermissions($editorPermissions);
    }
}
