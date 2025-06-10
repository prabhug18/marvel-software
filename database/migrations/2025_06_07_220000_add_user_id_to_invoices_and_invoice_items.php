<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('customer_id');
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
