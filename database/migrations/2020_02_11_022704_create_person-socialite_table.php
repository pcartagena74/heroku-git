<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonSocialiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person-socialite', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true);
            $table->integer('personID')->unsigned()->default(1);
            $table->string('providerID', 45);
            $table->string('providerName', 45);
            $table->string('token');
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('deleted_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        DB::statement('ALTER TABLE `person-socialite` ADD INDEX `ps.personID_idx` (`personID`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('person-socialite');
    }
}
