<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('date_history', function (Blueprint $table) {
            $table->integer('personID')->primary();
            $table->dateTime('RelDate1')->nullable();
            $table->dateTime('RelDate2')->nullable();
            $table->dateTime('RelDate3')->nullable();
            $table->dateTime('RelDate4')->nullable();
            $table->dateTime('RelDate5')->nullable();
            $table->dateTime('RelDate6')->nullable();
            $table->dateTime('RelDate7')->nullable();
            $table->dateTime('RelDate8')->nullable();
            $table->dateTime('RelDate9')->nullable();
            $table->dateTime('RelDate10')->nullable();
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('date_history');
    }
}
