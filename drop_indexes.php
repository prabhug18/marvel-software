<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$table = 'customers';
echo "Listing all indexes for $table...\n";

try {
    $indexes = DB::select("SHOW INDEX FROM $table");
    
    foreach ($indexes as $index) {
        $name = $index->Key_name;
        $column = $index->Column_name;
        
        echo "Found index: $name on column: $column\n";
        
        if ($name === 'PRIMARY') continue;
        
        if (in_array($column, ['email', 'mobile_no'])) {
            try {
                DB::statement("ALTER TABLE $table DROP INDEX $name");
                echo "--> Successfully dropped index: $name\n";
            } catch (\Exception $e) {
                // If dropping by name fails, try dropping by column-based dropUnique if possible, 
                // but via raw SQL it's usually:
                echo "--> Failed to drop index $name: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Done.\n";
} catch (\Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
