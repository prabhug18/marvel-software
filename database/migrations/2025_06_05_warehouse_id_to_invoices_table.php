<?php
// database/migrations/2025_06_05_warehouse_id_to_invoices_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
    }
};
