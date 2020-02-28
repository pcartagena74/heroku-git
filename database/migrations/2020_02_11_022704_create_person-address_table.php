<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonAddressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('person-address', function(Blueprint $table)
		{
			$table->integer('addrID', true);
			$table->integer('personID')->index('pa-personID_idx');
			$table->string('addrTYPE', 25)->nullable();
			$table->string('addr1');
			$table->string('addr2')->nullable();
			$table->string('city', 50);
			$table->string('state', 30);
			$table->string('zip', 10);
			$table->integer('cntryID')->nullable()->default(228);
			$table->string('country', 45)->nullable();
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
		Schema::drop('person-address');
	}

}
