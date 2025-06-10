<?php
// database/migrations/2025_06_05_000002_create_invoice_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('invoice_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('action'); // created, updated, deleted
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_logs');
    }
};
