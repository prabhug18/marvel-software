<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop unique indexes on customers.email and customers.mobile_no if they exist.
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Attempt to drop named unique indexes. If they don't exist, ignore the exception.
            try {
                $table->dropUnique('customers_email_unique');
            } catch (\Exception $e) {
                // no-op
            }
            try {
                $table->dropUnique('customers_mobile_no_unique');
            } catch (\Exception $e) {
                // no-op
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Recreate the unique indexes on email and mobile_no.
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            try {
                $table->unique('email');
            } catch (\Exception $e) {
                // no-op
            }
            try {
                $table->unique('mobile_no');
            } catch (\Exception $e) {
                // no-op
            }
        });
    }
};
