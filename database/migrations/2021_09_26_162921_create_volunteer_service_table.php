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
            $table->id();
            $table->integer('volunteer_role_id')->unsigned();
            $table->foreign('volunteer_role_id')->references('id')->on('volunteer_roles');
            $table->integer('orgID')->unsigned();
            $table->foreign('orgID')->references('orgID')->on('organization');
            $table->integer('personID')->unsigned();
            $table->foreign('personID')->references('personID')->on('person');
            $table->dateTime('roleStartDate')->useCurrent();
            $table->dateTime('roleEndDate')->nullable();
            $table->string('title_override')->nullable()->default(null);
            $table->timestamps();
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
