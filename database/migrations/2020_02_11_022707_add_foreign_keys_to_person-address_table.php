<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPersonAddressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('person-address', function(Blueprint $table)
		{
			$table->foreign('personID', 'pa-personID')->references('personID')->on('person')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('person-address', function(Blueprint $table)
		{
			$table->dropForeign('pa-personID');
		});
	}

}