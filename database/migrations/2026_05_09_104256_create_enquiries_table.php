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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('enquiry_number')->unique();
            $table->string('name');
            $table->string('mobile_no');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('source')->nullable();
            $table->text('product_interest')->nullable();
            $table->string('brand_interest')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['new', 'contacted', 'converted', 'closed'])->default('new');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
