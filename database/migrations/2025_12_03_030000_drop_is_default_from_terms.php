<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropIsDefaultFromTerms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('terms')) return;

        Schema::table('terms', function (Blueprint $table) {
            if (Schema::hasColumn('terms', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('terms')) return;

        Schema::table('terms', function (Blueprint $table) {
            if (!Schema::hasColumn('terms', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('content');
            }
        });
    }
}
