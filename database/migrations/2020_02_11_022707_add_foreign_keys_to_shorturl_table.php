<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToShorturlTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('shorturl', function(Blueprint $table)
		{
			$table->foreign('orgID', 'su-orgID')->references('orgID')->on('organization')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('shorturl', function(Blueprint $table)
		{
			$table->dropForeign('su-orgID');
		});
	}

}
