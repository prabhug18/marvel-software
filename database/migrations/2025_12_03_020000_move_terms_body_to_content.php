<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MoveTermsBodyToContent extends Migration
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
            if (!Schema::hasColumn('terms', 'content')) {
                $table->longText('content')->nullable()->after('id');
            }
        });

        // Copy existing body to content if present
        try {
            DB::statement("UPDATE terms SET content = body WHERE content IS NULL AND body IS NOT NULL");
        } catch (\Exception $e) {
            // ignore
        }

        // drop old columns if they exist
        Schema::table('terms', function (Blueprint $table) {
            if (Schema::hasColumn('terms', 'body')) {
                $table->dropColumn('body');
            }
            if (Schema::hasColumn('terms', 'title')) {
                $table->dropColumn('title');
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
            if (!Schema::hasColumn('terms', 'body')) {
                $table->longText('body')->nullable()->after('content');
            }
            if (!Schema::hasColumn('terms', 'title')) {
                $table->string('title')->nullable()->after('content');
            }
        });

        try {
            DB::statement("UPDATE terms SET body = content WHERE body IS NULL AND content IS NOT NULL");
        } catch (\Exception $e) {
            // ignore
        }

        Schema::table('terms', function (Blueprint $table) {
            if (Schema::hasColumn('terms', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
}
