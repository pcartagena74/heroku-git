<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonPhoneTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('person-phone', function(Blueprint $table)
		{
			$table->integer('phoneID', true);
			$table->integer('personID')->unsigned()->index('pp-personID_idx');
			$table->string('phoneNumber', 25)->nullable();
			$table->string('phoneType', 10)->nullable();
			$table->integer('creatorID')->nullable()->default(1);
			$table->timestamp('createDate')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->nullable()->default(1);
			$table->timestamp('updateDate')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
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
		Schema::drop('person-phone');
	}

}
