<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRegFinanceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('reg-finance', function(Blueprint $table)
		{
			$table->foreign('eventID', 'rf-eventID')->references('eventID')->on('org-event')->onUpdate('NO ACTION')->onDelete('CASCADE');
			$table->foreign('personID', 'rf-personID')->references('personID')->on('person')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('reg-finance', function(Blueprint $table)
		{
			$table->dropForeign('rf-eventID');
			$table->dropForeign('rf-personID');
		});
	}

}
