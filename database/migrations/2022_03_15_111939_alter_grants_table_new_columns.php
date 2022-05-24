<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGrantsTableNewColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grants', function (Blueprint $table) {
            $table->dropUnique('grants_role_name_unique');
            $table->string('department_name', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'grants',
            function (Blueprint $table) {
                $table->string('role_name', 50)->unique()->change();
                $table->string('department_name', 20)->nullable()->change();
            }
        );
    }
}
