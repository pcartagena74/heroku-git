<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRegFinanceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reg-finance', function(Blueprint $table)
		{
			$table->integer('regID', true);
			$table->integer('eventID')->index('rf-eventID_idx');
			$table->integer('ticketID')->nullable()->default(0);
			$table->integer('personID')->unsigned()->index('rf-personID_idx');
			$table->integer('seats')->default(1);
			$table->string('pmtType', 45)->default('pending');
			$table->string('confirmation', 45)->default('pending');
			$table->boolean('pmtRecd')->default(0);
			$table->string('status', 45)->default('pending');
			$table->float('cost', 10)->default(0.00);
			$table->float('ccFee', 7)->default(0.00);
			$table->float('handleFee', 7)->default(0.00);
			$table->float('refundAmt', 10)->default(0.00);
			$table->float('orgAmt', 10)->default(0.00);
			$table->string('discountCode', 45)->nullable()->default('N/A');
			$table->float('discountAmt', 10)->default(0.00);
			$table->string('token')->nullable()->index('rf-token_idx');
			$table->string('stripeChargeID')->nullable();
			$table->boolean('isGroupReg')->default(0);
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->dateTime('cancelDate')->nullable();
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
		Schema::drop('reg-finance');
	}

}
