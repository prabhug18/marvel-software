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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('formatted_id')->nullable()->after('id')->unique();
            $table->string('alternative_no')->nullable()->after('mobile_no');
        });

        // Retroactively generate formatted_id for existing customers
        $customers = \Illuminate\Support\Facades\DB::table('customers')->get();
        foreach ($customers as $customer) {
            $prefix = ($customer->customer_type === 'Dealer') ? 'DLR-' : 'CUST-';
            $formatted_id = $prefix . str_pad($customer->id, 3, '0', STR_PAD_LEFT);
            \Illuminate\Support\Facades\DB::table('customers')
                ->where('id', $customer->id)
                ->update(['formatted_id' => $formatted_id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['formatted_id', 'alternative_no']);
        });
    }
};
