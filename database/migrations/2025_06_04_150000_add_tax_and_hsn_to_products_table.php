<?php
// Add tax_percentage and hsn_code to products table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('hsn_code')->nullable();
            $table->decimal('tax_percentage', 5, 2)->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['hsn_code', 'tax_percentage']);
        });
    }
};
