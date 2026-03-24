<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class SetHeadOfficeWarehouseId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ensure the settings table has an entry for head_office_warehouse_id = 1
        $now = date('Y-m-d H:i:s');
        DB::table('settings')->updateOrInsert(
            ['key' => 'head_office_warehouse_id'],
            ['value' => '1', 'updated_at' => $now, 'created_at' => $now]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('key', 'head_office_warehouse_id')->delete();
    }
}
