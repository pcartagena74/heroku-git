<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonEmailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('person-email', function(Blueprint $table)
		{
			$table->integer('emailID', true);
			$table->integer('personID')->index('pe-personID_idx');
			$table->string('emailTYPE', 25)->nullable();
			$table->string('emailADDR')->unique('emailADDR');
			$table->boolean('isPrimary')->default(0);
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
		Schema::drop('person-email');
	}

}
