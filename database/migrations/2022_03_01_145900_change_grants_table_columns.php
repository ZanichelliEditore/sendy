<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeGrantsTableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // INFO : Create a backup table
        Schema::rename("grants", "grants_backup");

        Schema::create(
            'grants',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('role_name', 50)->unique();
                $table->string('department_name', 20)->nullable();
                $table->text('grant');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grants');
        Schema::rename("grants_backup", "grants");
    }
}
