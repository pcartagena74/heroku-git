<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVolunteerRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('volunteer_roles', function (Blueprint $table) {
            $table->id();
            $table->integer('reports_to')->unsigned();
            $table->foreign('reports_to')->references('id')->on('volunteer_roles');
            $table->integer('orgID')->unsigned();
            $table->foreign('orgID')->references('orgID')->on('organization');
            $table->string('title');
            $table->boolean('has_reports')->default(0);
            $table->string('jd_URL')->nullable()->default(null);
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
        Schema::dropIfExists('volunteer_roles');
    }
}