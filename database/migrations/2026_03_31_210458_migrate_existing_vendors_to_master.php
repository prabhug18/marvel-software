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
        // 1. Find all unique vendor names in the stocks table
        $vendorNames = \DB::table('stocks')
            ->whereNotNull('purchased_from')
            ->where('purchased_from', '!=', '')
            ->distinct()
            ->pluck('purchased_from');

        foreach ($vendorNames as $name) {
            // 2. Create a Vendor record if it doesn't already exist
            $vendorId = \DB::table('vendors')->insertGetId([
                'name' => trim($name),
                'status_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Update all matching stock records to link them to the new vendor master
            \DB::table('stocks')
                ->where('purchased_from', $name)
                ->update(['vendor_id' => $vendorId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            //
        });
    }
};
