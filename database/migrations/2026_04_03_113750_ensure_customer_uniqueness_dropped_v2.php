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
        $indexes = DB::select("SHOW INDEX FROM $table");
        foreach ($indexes as $index) {
            if ($index->Key_name === 'PRIMARY') continue;
            if (in_array($index->Column_name, ['email', 'mobile_no'])) {
                try {
                    DB::statement("ALTER TABLE $table DROP INDEX `{$index->Key_name}`");
                } catch (\Exception $e) {
                    // Fail silently if already dropped
                }
            }
        }
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
