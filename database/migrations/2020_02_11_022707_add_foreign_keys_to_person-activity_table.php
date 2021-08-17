<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPersonActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person-activity', function (Blueprint $table) {
            $table->foreign('orgID', 'pact-orgID')->references('orgID')->on('organization')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person-activity', function (Blueprint $table) {
            $table->dropForeign('pact-orgID');
        });
    }
}
