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
        Schema::table('product_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('performed_by')->nullable()->after('product_id');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->dropColumn('performed_by');
        });
    }
};
