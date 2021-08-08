<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person-activity', function (Blueprint $table) {
            $table->integer('activityID', true);
            $table->integer('orgID')->index('orgID_idx');
            $table->integer('personID');
            $table->integer('detailID')->nullable();
            $table->integer('activityTypeID');
            $table->dateTime('activityDate');
            $table->text('contentNote')->nullable();
            $table->integer('creatorID')->nullable()->default(1);
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('updaterID')->nullable()->default(1);
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('person-activity');
    }
}
