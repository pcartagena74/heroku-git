<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRegSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reg-session', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true);
            $table->integer('regID')->index('rs-regID_idx');
            $table->integer('personID')->unsigned()->default(1)->index('rs-personID_idx');
            $table->integer('eventID')->index('rs-eventID_idx');
            $table->integer('confDay')->default(0);
            $table->integer('hasAttended')->default(0);
            $table->integer('sessionID')->index('rs-sessionID_idx');
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('creatorID')->default(1);
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('updaterID')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reg-session');
    }
}
