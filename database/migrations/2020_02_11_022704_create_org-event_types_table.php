<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgEventTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('org-event_types', function (Blueprint $table) {
            $table->integer('etID', true);
            $table->integer('orgID')->index('et-orgID_idx');
            $table->string('etName', 75);
            $table->boolean('isActive')->default(1);
            $table->integer('creatorID')->default(1);
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('updaterID')->default(1);
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('org-event_types');
    }
}
