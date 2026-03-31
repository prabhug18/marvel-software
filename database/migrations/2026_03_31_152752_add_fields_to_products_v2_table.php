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
        Schema::table('products', function (Blueprint $table) {
            $table->string('capacity')->nullable()->after('model_no');
            $table->string('remarks')->nullable()->after('specification');
            $table->integer('foc_months')->nullable()->after('remarks');
            $table->integer('prorata_months')->nullable()->after('foc_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['capacity', 'remarks', 'foc_months', 'prorata_months']);
        });
    }
};
