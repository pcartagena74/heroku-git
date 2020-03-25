<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailBuilderTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_block_categorys', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name', 50)->nullable();
        });
        Schema::create('email_blocks', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('cat_id');
            $table->string('icon', 20)->nullable();
            $table->string('property', 100)->nullable();
            $table->string('name', 70)->nullable();
            $table->text('html')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(1);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_block_categorys');
        Schema::dropIfExists('email_blocks');
    }
}
