<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventLocationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event-location', function(Blueprint $table)
		{
			$table->integer('locID', true);
			$table->integer('orgID')->index('el-orgID_idx');
			$table->string('locName', 50);
			$table->string('addr1')->nullable();
			$table->string('addr2')->nullable();
			$table->string('city', 50)->nullable();
			$table->string('state', 10)->nullable();
			$table->string('zip', 10)->nullable();
			$table->integer('countryID')->default(228);
			$table->text('locNote')->nullable();
			$table->boolean('isVirtual')->default(0);
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->softDeletes();
			$table->boolean('isDeleted')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('event-location');
	}

}
