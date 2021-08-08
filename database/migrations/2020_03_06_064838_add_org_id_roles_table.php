<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrgIdRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'orgID')) {
                $table->integer('orgID')->default(10);
            }
            $table->dropUnique('un_id_name');
            $table->unique(['orgID', 'name'], 'uq_orgID_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->drop('orgID');
            $table->dropUnique(['orgID', 'name'], 'uq_orgID_name');
        });
    }
}
