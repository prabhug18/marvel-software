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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Nullable foreign keys for product and user (if your app stores them per item)
            if (! Schema::hasColumn('invoice_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('invoice_id');
                // add index; foreign constraint optional depending on your environment
                // $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            }
            if (! Schema::hasColumn('invoice_items', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('product_id');
            }

            // serial / imei or similar
            if (! Schema::hasColumn('invoice_items', 'serial_no')) {
                $table->text('serial_no')->nullable()->after('model');
            }

            // HSN (optional)
            if (! Schema::hasColumn('invoice_items', 'hsn_code')) {
                $table->string('hsn_code', 64)->nullable()->after('serial_no');
            }

            // add tax split columns
            if (! Schema::hasColumn('invoice_items', 'cgst_amount')) {
                $table->decimal('cgst_amount', 10, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('invoice_items', 'sgst_amount')) {
                $table->decimal('sgst_amount', 10, 2)->default(0)->after('cgst_amount');
            }
            if (! Schema::hasColumn('invoice_items', 'igst_amount')) {
                $table->decimal('igst_amount', 10, 2)->default(0)->after('sgst_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'igst_amount')) {
                $table->dropColumn('igst_amount');
            }
            if (Schema::hasColumn('invoice_items', 'sgst_amount')) {
                $table->dropColumn('sgst_amount');
            }
            if (Schema::hasColumn('invoice_items', 'cgst_amount')) {
                $table->dropColumn('cgst_amount');
            }
            if (Schema::hasColumn('invoice_items', 'hsn_code')) {
                $table->dropColumn('hsn_code');
            }
            if (Schema::hasColumn('invoice_items', 'serial_no')) {
                $table->dropColumn('serial_no');
            }
            if (Schema::hasColumn('invoice_items', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('invoice_items', 'product_id')) {
                $table->dropColumn('product_id');
            }
        });
    }
};
