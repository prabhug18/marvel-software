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
            // Drop unique indexes on email and mobile_no if they exist.
            // Using dropUnique([column]) is safer as Laravel handles the naming convention.
            // Wrap in try-catch to ignore if they are already dropped.
            try {
                $table->dropUnique(['email']);
            } catch (\Exception $e) {
                // Already dropped or different name
            }

            try {
                $table->dropUnique(['mobile_no']);
            } catch (\Exception $e) {
                // Already dropped or different name
            }

            // Fallback: try common manual naming patterns if the above fails
            try {
                $table->dropUnique('customers_email_unique');
            } catch (\Exception $e) {}
            try {
                $table->dropUnique('customers_mobile_no_unique');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Re-add unique constraints if rolled back (optional, based on preference)
            // $table->unique('email');
            // $table->unique('mobile_no');
        });
    }
};
