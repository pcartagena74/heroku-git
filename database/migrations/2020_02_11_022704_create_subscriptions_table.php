<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubscriptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subscriptions', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id');
			$table->string('name')->nullable();
			$table->string('stripe_id')->nullable();
			$table->string('stripe_plan')->nullable();
			$table->integer('quantity')->nullable();
			$table->timestamp('trial_ends_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('ends_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subscriptions');
	}

}
