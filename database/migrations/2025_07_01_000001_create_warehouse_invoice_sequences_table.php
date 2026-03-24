<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->unique();
            $table->bigInteger('current_number')->default(1001); // Set your default start number
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_invoice_sequences');
    }
};
