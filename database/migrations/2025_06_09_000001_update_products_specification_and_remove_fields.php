<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['processor', 'memory', 'operating_system']);
            $table->text('specification')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('processor')->nullable();
            $table->string('memory')->nullable();
            $table->string('operating_system')->nullable();
            $table->dropColumn('specification');
        });
    }
};
