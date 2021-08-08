<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRsSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rs-survey', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('regID');
            $table->integer('personID');
            $table->integer('sessionID');
            $table->integer('engageResponse');
            $table->integer('takeResponse');
            $table->integer('contentResponse');
            $table->integer('styleResponse');
            $table->text('favoriteResponse', 16777215)->nullable();
            $table->text('suggestResponse', 16777215)->nullable();
            $table->text('contactResponse', 16777215)->nullable();
            $table->integer('wantsContact')->default(0);
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::drop('rs-survey');
    }
}
