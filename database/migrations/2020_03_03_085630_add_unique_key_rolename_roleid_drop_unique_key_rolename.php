<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueKeyRolenameRoleidDropUniqueKeyRolename extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_name_unique');
            $table->unique(['id', 'name'], 'un_id_name');
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
            $table->unique('name', 'roles_name_unique');
            $table->dropUnique('un_id_name');
        });
    }
}
