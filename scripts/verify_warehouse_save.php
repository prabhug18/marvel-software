<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;

$c = new Customer();
$c->name = 'Verify Test ' . time();
$c->email = 'verify' . time() . '@example.test';
$c->mobile_no = str_pad(rand(0,9999999999),10,'9');
$c->address = 'Test Addr';
$c->state_id = 35;
$c->city_id = 1;
$c->pincode = '641001';
$c->warehouse_id = 42; // test value
$c->user_id = 1;
$c->save();

$retrieved = Customer::find($c->id);
echo "Inserted ID: {$c->id}\n";
echo "Stored warehouse_id: " . ($retrieved->warehouse_id ?? 'null') . "\n";
