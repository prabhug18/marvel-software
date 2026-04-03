<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Starting Customer Table Fix...\n";

try {
    // 1. Find all index names for the 'customers' table
    $indexes = DB::select("SHOW INDEX FROM customers");
    $dropped = [];

    foreach ($indexes as $index) {
        $name = $index->Key_name;
        $column = $index->Column_name;
        
        if ($name === 'PRIMARY') continue;

        // If the index involves email or mobile_no, drop it.
        if (in_array($column, ['email', 'mobile_no'])) {
            if (!in_array($name, $dropped)) {
                try {
                    DB::statement("ALTER TABLE customers DROP INDEX `$name` ");
                    echo "Dropped index: $name (Column: $column)\n";
                    $dropped[] = $name;
                } catch (\Exception $e) {
                    echo "Failed to drop index $name: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    // 2. Set empty strings to NULL
    $affected = DB::table('customers')->where('email', '')->update(['email' => null]);
    echo "Updated $affected records from '' to NULL\n";

    // 3. Make email nullable
    DB::statement("ALTER TABLE customers MODIFY email VARCHAR(255) NULL");
    echo "Made email column NULLABLE via ALTER TABLE\n";

    echo "Fix Complete.\n";
} catch (\Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
