<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMediablesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('mediables', function(Blueprint $table)
		{
			$table->foreign('media_id')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('mediables', function(Blueprint $table)
		{
			$table->dropForeign('mediables_media_id_foreign');
		});
	}

}
