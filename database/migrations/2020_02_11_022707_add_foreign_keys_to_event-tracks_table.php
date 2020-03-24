<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEventTracksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('event-tracks', function(Blueprint $table)
		{
			$table->foreign('eventID', 'ek-eventID')->references('eventID')->on('org-event')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('event-tracks', function(Blueprint $table)
		{
			$table->dropForeign('ek-eventID');
		});
	}

}
