<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShorturlTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shorturl', function(Blueprint $table)
		{
			$table->integer('urlID', true);
			$table->integer('orgID')->index('su-orgID_idx');
			$table->string('sourceURL', 45)->unique('sourceURL_UNIQUE');
			$table->text('targetURL', 16777215);
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
		Schema::drop('shorturl');
	}

}
