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
            $table->integer('id')->unsigned()->autoIncrement();
            $table->integer('pid')->nullable()->unsigned();
            $table->foreign('pid')->references('id')->on('volunteer_roles');
            $table->integer('orgID');
            $table->foreign('orgID')->references('orgID')->on('organization');
            $table->string('title');
            $table->string('title_override')->nullable()->default(null);
            $table->boolean('has_reports')->default(0);
            $table->string('jd_URL')->nullable()->default(null);
            $table->dateTime('created_at')->default(DB::raw('now()'));
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
        Schema::dropIfExists('volunteer_roles');
    }
}