<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventSessionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event-sessions', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->integer('sessionID');
			$table->integer('trackID')->default(0);
			$table->integer('eventID')->index('es-eventID');
			$table->integer('ticketID')->default(0);
			$table->string('sessionName')->nullable();
			$table->string('sessionSpeakers')->nullable();
			$table->integer('confDay')->default(0);
			$table->dateTime('start')->nullable();
			$table->dateTime('end')->nullable();
			$table->integer('order')->default(0);
			$table->float('creditAmt', 7)->default(0.00);
			$table->string('creditArea', 50)->nullable();
			$table->float('leadAmt', 4)->nullable()->default(0.00);
			$table->float('stratAmt', 4)->nullable()->default(0.00);
			$table->float('techAmt', 4)->nullable()->default(0.00);
			$table->text('sessionAbstract')->nullable();
			$table->integer('maxAttendees')->nullable()->default(0);
			$table->integer('regCount')->default(0);
			$table->integer('isLinked')->default(0)->comment('Quasi boolean, overloaded with sessionID of the session to which current session is linked.   When sessionID = isLinked, that is the session that is NOT deleted.');
			$table->boolean('isRegSwitchProhibited')->default(0);
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->softDeletes();
			$table->increments('sessionID')->change();//make it auto increment
            $table->primary(['sessionID','trackID','eventID']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('event-sessions');
	}

}
