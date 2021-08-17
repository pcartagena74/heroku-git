<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUszipsv11Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uszipsv11', function (Blueprint $table) {
            $table->string('zip', 5)->primary();
            $table->float('lat', 10, 0);
            $table->float('lng', 10, 0);
            $table->string('city', 100);
            $table->string('state', 2);
            $table->string('zcta', 5)->nullable();
            $table->string('parent_zcta', 5)->nullable();
            $table->string('county_fips', 5)->nullable();
            $table->string('county_name', 100);
            $table->float('county_weight', 10, 0)->nullable();
            $table->string('all_county_weights', 50)->nullable();
            $table->string('imprecise', 5)->nullable();
            $table->string('military', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('uszipsv11');
    }
}
