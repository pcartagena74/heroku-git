<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgDiscountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('org-discounts', function(Blueprint $table)
		{
			$table->integer('discountID', true);
			$table->integer('orgID')->index('d-orgID_idx');
			$table->string('discountCODE', 45)->nullable();
			$table->integer('percent')->default(0);
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('org-discounts');
	}

}
