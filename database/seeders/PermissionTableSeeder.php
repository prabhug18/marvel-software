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
        //
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'warehouse-list',
            'warehouse-create',
            'warehouse-edit',
            'warehouse-delete',
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'stock-list',
            'stock-create',
            'stock-edit',
            'stock-delete',
            'price-list',
            'price-create',
            'price-edit',
            'price-delete',
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
            'brand-list',
            'brand-create',
            'brand-edit',
            'brand-delete',
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',
        ];
         
         foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
