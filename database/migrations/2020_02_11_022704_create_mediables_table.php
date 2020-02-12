<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediablesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mediables', function(Blueprint $table)
		{
			$table->integer('media_id')->unsigned();
			$table->string('mediable_type');
			$table->integer('mediable_id')->unsigned();
			$table->string('tag')->index();
			$table->integer('order')->unsigned()->index();
			$table->primary(['media_id','mediable_type','mediable_id','tag']);
			$table->index(['mediable_id','mediable_type']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mediables');
	}

}
