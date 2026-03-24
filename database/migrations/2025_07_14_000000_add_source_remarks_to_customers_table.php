<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('source')->nullable()->after('name');
            $table->string('customer_type')->nullable()->after('source');
            $table->text('remarks')->nullable()->after('customer_type');
        });
    }
    public function down() {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['source', 'customer_type', 'remarks']);
        });
    }
};
