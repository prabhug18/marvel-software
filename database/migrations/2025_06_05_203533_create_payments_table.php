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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('customer_id');
            $table->string('customer_name');
            $table->string('invoice_number')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->decimal('grand_total', 15, 2)->nullable();
            $table->decimal('balance_amount', 15, 2)->nullable();
            $table->decimal('paid_amount', 15, 2);
            $table->string('payment_mode');
            $table->date('payment_date');
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
