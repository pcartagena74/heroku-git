<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEventLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event-location', function (Blueprint $table) {
            $table->foreign('orgID', 'el-orgID')->references('orgID')->on('organization')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event-location', function (Blueprint $table) {
            $table->dropForeign('el-orgID');
        });
    }
}
