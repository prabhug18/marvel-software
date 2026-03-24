<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Use raw SQL to alter column to LONGTEXT to avoid doctrine/dbal dependency
        DB::statement("ALTER TABLE `customer_logs` MODIFY `description` LONGTEXT NULL");
    }

    public function down()
    {
        // Revert to TEXT (may truncate data if longer than TEXT)
        DB::statement("ALTER TABLE `customer_logs` MODIFY `description` TEXT NULL");
    }
};
