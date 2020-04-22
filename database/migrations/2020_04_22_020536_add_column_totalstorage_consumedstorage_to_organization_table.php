<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTotalstorageConsumedstorageToOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization', function (Blueprint $table) {
            $table->integer('total_storage')->unsigned()->default(512);
            $table->integer('consumed_storage')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization', function (Blueprint $table) {
            $table->drop('total_storage');
            $table->drop('consumed_storage');
        });
    }
}
