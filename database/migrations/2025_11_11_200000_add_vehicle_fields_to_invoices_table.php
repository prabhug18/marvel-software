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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('vehicle_type')->nullable()->after('warehouse_id');
            $table->text('vehicle_details')->nullable()->after('vehicle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'vehicle_details')) {
                $table->dropColumn('vehicle_details');
            }
            if (Schema::hasColumn('invoices', 'vehicle_type')) {
                $table->dropColumn('vehicle_type');
            }
        });
    }
};
