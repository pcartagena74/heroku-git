<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEventTicketsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('event-tickets', function(Blueprint $table)
		{
			$table->foreign('eventID', 'et-eventID')->references('eventID')->on('org-event')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('event-tickets', function(Blueprint $table)
		{
			$table->dropForeign('et-eventID');
		});
	}

}
