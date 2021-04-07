<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event-tracks', function (Blueprint $table) {
            $table->integer('trackID', true);
            $table->integer('eventID')->index('et-eventID_idx');
            $table->string('trackName', 100);
            $table->integer('creatorID')->default(1);
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('updaterID')->default(1);
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('event-tracks');
    }
}
