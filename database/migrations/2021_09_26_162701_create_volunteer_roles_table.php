<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('volunteer_roles', function (Blueprint $table) {
            $table->integer('id')->unsigned()->autoIncrement();
            $table->integer('pid')->nullable()->unsigned();
            $table->foreign('pid')->references('id')->on('volunteer_roles');
            $table->integer('orgID');
            $table->foreign('orgID')->references('orgID')->on('organization');
            $table->boolean('prefix_override')->default(0);
            $table->string('title')->default('role');
            $table->string('title_override')->nullable()->default(null);
            $table->string('jd_URL')->nullable()->default(null);
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
    public function down(): void
    {
        Schema::dropIfExists('volunteer_roles');
    }
};
