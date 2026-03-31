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
        Schema::table('stocks', function (Blueprint $table) {
            $table->date('purchase_date')->nullable();
            $table->string('remarks')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('purchased_from')->nullable();
            $table->decimal('purchase_rate', 10, 2)->nullable();
            $table->string('status')->default('In Stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['purchase_date', 'remarks', 'serial_no', 'purchased_from', 'purchase_rate', 'status']);
        });
    }
};
