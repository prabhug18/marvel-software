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
        Schema::table('customers', function (Blueprint $table) {
            // 1. Broadly drop unique indexes by their common names to avoid integrity errors.
            try {
                $table->dropUnique('customers_email_unique');
            } catch (\Exception $e) {}

            try {
                $table->dropUnique('customers_mobile_no_unique');
            } catch (\Exception $e) {}
            
            try {
                $table->dropUnique(['email']);
            } catch (\Exception $e) {}

            try {
                $table->dropUnique(['mobile_no']);
            } catch (\Exception $e) {}
        });

        // 2. Data Cleanup: Set existing empty string emails to NULL to avoid duplicate key issues during the change.
        DB::table('customers')->where('email', '')->update(['email' => null]);

        // 3. Now make the email field nullable
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }

};
