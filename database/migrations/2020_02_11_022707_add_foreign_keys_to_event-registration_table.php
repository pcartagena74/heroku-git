<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEventRegistrationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('event-registration', function(Blueprint $table)
		{
			$table->foreign('eventID', 'er-eventID')->references('eventID')->on('org-event')->onUpdate('NO ACTION')->onDelete('CASCADE');
			$table->foreign('personID', 'er-personID')->references('personID')->on('person')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('ticketID', 'er-ticketID')->references('ticketID')->on('event-tickets')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('event-registration', function(Blueprint $table)
		{
			$table->dropForeign('er-eventID');
			$table->dropForeign('er-personID');
			$table->dropForeign('er-ticketID');
		});
	}

}
