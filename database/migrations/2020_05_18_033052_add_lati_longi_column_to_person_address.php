<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLatiLongiColumnToPersonAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person-address', function (Blueprint $table) {
            $table->float('lati', 10, 6);
            $table->float('longi', 10, 6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person-address', function (Blueprint $table) {
            $table->dropColumn('lati', 'longi');
        });
    }
}