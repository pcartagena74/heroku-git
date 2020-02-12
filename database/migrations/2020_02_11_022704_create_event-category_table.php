<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event-category', function(Blueprint $table)
		{
			$table->integer('catID', true);
			$table->integer('orgID')->index('ec-orgID_idx');
			$table->string('catTXT', 150);
			$table->boolean('isActive')->default(1);
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('event-category');
	}

}
