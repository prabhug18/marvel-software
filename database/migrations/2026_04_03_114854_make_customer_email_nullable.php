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
        $table = 'customers';

        // 1. Robustly drop any unique indexes on email/mobile_no using raw SQL
        // This is safer than dropUnique([]) because it handles naming mismatches.
        $indexes = DB::select("SHOW INDEX FROM $table");
        foreach ($indexes as $index) {
            if ($index->Key_name === 'PRIMARY') continue;
            if (in_array($index->Column_name, ['email', 'mobile_no'])) {
                try {
                    DB::statement("ALTER TABLE $table DROP INDEX `{$index->Key_name}`");
                } catch (\Exception $e) {
                    // Ignore if already dropped
                }
            }
        }

        // 2. Data Cleanup: Set existing empty string emails to NULL to avoid duplicate key issues
        DB::table($table)->where('email', '')->update(['email' => null]);

        // 3. Make the email field nullable
        Schema::table($table, function (Blueprint $table) {
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
