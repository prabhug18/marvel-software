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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->unique();
            $table->unsignedBigInteger('enquiry_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('name');
            $table->string('mobile_no');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('source')->nullable();
            $table->text('product_interest')->nullable();
            $table->string('brand_interest')->nullable();
            $table->decimal('expected_value', 10, 2)->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['new', 'follow_up', 'negotiation', 'converted', 'lost'])->default('new');
            $table->date('next_follow_up')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('enquiry_id')->references('id')->on('enquiries')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
