<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->string('disk', 32);
            $table->string('directory');
            $table->string('filename');
            $table->string('extension', 32);
            $table->string('mime_type', 128);
            $table->string('aggregate_type', 32)->index();
            $table->integer('size')->unsigned();
            $table->timestamps();
            $table->unique(['disk', 'directory', 'filename', 'extension']);
            $table->index(['disk', 'directory']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('media');
    }
}
