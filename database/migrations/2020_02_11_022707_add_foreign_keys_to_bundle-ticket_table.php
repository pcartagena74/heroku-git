<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBundleTicketTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bundle-ticket', function(Blueprint $table)
		{
			$table->foreign('ticketID', 'bt-ticketID')->references('ticketID')->on('event-tickets')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
		// DB::raw('alter table `bundle-ticket` add constraint `bt-ticketID`
		// 	foreign key (`ticketID`) references `event-tickets` (`ticketID`)
		// 	on delete NO ACTION
		// 	on update NO ACTION');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('bundle-ticket', function(Blueprint $table)
		{
			$table->dropForeign('bt-ticketID');
		});
	}

}
