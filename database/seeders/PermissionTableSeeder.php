<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'dashboard',
            'role',
            'warehouse',
            'product',
            'stock',
            'customer',
            'brand',
            'category',
            'invoice',
            'payment',
            'source',
            'vendor',
            'user',
            'settings',
        ];

        $actions = ['list', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                // Special case: dashboard usually only has list, settings usually only has edit/list
                if ($module === 'dashboard' && $action !== 'list') continue;
                
                Permission::firstOrCreate([
                    'name' => $module . '-' . $action,
                    'guard_name' => 'web'
                ]);
            }
        }

        // Auto-assign to Admin role
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $permissions = Permission::pluck('id', 'id')->all();
        $role->syncPermissions($permissions);
    }
}
