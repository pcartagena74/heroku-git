<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventsessionSpeakerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventsession_speaker', function (Blueprint $table) {
            $table->integer('eventsession_id');
            $table->integer('speaker_id');
            $table->primary(['eventsession_id', 'speaker_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('eventsession_speaker');
    }
}
