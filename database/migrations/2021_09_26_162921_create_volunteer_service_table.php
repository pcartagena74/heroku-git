<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVolunteerServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('volunteer_service', function (Blueprint $table) {
            $table->integer('id')->unsigned()->autoIncrement();
            $table->integer('volunteer_role_id')->unsigned();
            $table->foreign('volunteer_role_id')->references('id')->on('volunteer_roles');
            $table->integer('orgID');
            $table->foreign('orgID')->references('orgID')->on('organization');
            $table->integer('personID');
            $table->foreign('personID')->references('personID')->on('person');
            $table->dateTime('roleStartDate')->useCurrent();
            $table->dateTime('roleEndDate')->nullable();
            $table->string('title_save')->nullable()->default(null);
            $table->integer('creatorID')->default(0);
            $table->dateTime('created_at')->default(DB::raw('now()'));
            $table->integer('updaterID')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('volunteer_service');
    }
}
