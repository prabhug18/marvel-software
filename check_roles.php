<?php
// Tiny script to check roles and permissions
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$roles = \Spatie\Permission\Models\Role::with('permissions')->get();
foreach ($roles as $role) {
    echo $role->name . ": " . $role->permissions->pluck('name')->join(', ') . "\n";
}
