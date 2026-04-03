<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

echo "Starting Customer Resequencing...\n";

try {
    DB::beginTransaction();

    $types = ['Customer', 'Dealer'];

    foreach ($types as $type) {
        echo "Resequencing: $type\n";
        $customers = Customer::where('customer_type', $type)->orderBy('id')->get();
        $seq = 1;

        foreach ($customers as $customer) {
            $prefix = ($type === 'Dealer') ? 'DLR-' : 'CUST-';
            $formattedId = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
            
            $customer->type_sequence = $seq;
            $customer->formatted_id = $formattedId;
            $customer->save();
            
            echo "Updated Customer ID {$customer->id} to $formattedId\n";
            $seq++;
        }
    }

    DB::commit();
    echo "Resequencing complete.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
