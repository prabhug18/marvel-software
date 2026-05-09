<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $modules = ['enquiry', 'lead'];
        $actions = ['list', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => $module . '-' . $action,
                    'guard_name' => 'web'
                ]);
            }
        }

        // Auto-assign to Admin role if it exists
        $role = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        if ($role) {
            $permissions = \Spatie\Permission\Models\Permission::whereIn('name', [
                'enquiry-list', 'enquiry-create', 'enquiry-edit', 'enquiry-delete',
                'lead-list', 'lead-create', 'lead-edit', 'lead-delete'
            ])->get();
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'enquiry-list', 'enquiry-create', 'enquiry-edit', 'enquiry-delete',
            'lead-list', 'lead-create', 'lead-edit', 'lead-delete'
        ];
        \Spatie\Permission\Models\Permission::whereIn('name', $permissions)->delete();
    }
};
