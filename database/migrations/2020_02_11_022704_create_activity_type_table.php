<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_type', function (Blueprint $table) {
            $table->integer('orgID');
            $table->integer('activityTypeID', true);
            $table->string('typeLabel', 45);
            $table->integer('creatorID')->default(1);
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('updaterID')->default(1);
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
        Schema::drop('activity_type');
    }
}
