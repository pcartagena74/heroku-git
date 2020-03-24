<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventTicketsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event-tickets', function(Blueprint $table)
		{
			$table->integer('ticketID', true);
			$table->integer('eventID')->index('et-eventID_idx');
			$table->string('ticketLabel', 100);
			$table->dateTime('availabilityEndDate');
			$table->dateTime('earlyBirdEndDate')->nullable();
			$table->integer('earlyBirdPercent')->default(0);
			$table->float('memberBasePrice', 7)->default(0.00);
			$table->float('nonmbrBasePrice', 7)->default(0.00);
			$table->integer('maxAttendees')->nullable();
			$table->integer('regCount')->default(0);
			$table->integer('waitCount')->default(0);
			$table->boolean('isaBundle')->default(0);
			$table->boolean('isDiscountExempt')->default(0);
			$table->boolean('isSuppressed')->default(0);
			$table->boolean('anonCats')->default(0);
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('event-tickets');
	}

}
