<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEventDiscountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('event-discounts', function(Blueprint $table)
		{
			$table->foreign('eventID', 'ed-eventID')->references('eventID')->on('org-event')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('orgID', 'ed-orgID')->references('orgID')->on('organization')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('event-discounts', function(Blueprint $table)
		{
			$table->dropForeign('ed-eventID');
			$table->dropForeign('ed-orgID');
		});
	}

}
