<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPersonEmailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('person-email', function(Blueprint $table)
		{
			$table->foreign('personID', 'pe-personID')->references('personID')->on('person')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('person-email', function(Blueprint $table)
		{
			$table->dropForeign('pe-personID');
		});
	}

}
