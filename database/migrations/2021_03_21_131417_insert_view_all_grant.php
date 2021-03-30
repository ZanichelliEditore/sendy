<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InsertViewAllGrant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('grants')->insert([
            'role_id' => 12,
            'department_id' => 10,
            'grant' => 'VIEW_ALL'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('grants')->where([
            'role_id' => 12,
            'department_id' => 10,
            'grant' => 'VIEW_ALL'
        ])->delete();
    }
}
