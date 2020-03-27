<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->integer('id', true)->unsigned();
			$table->string('login')->unique('users_login_uindex');
			$table->string('name');
			$table->string('password')->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->dateTime('last_login')->nullable();
			$table->string('email');
			$table->string('stripe_id')->nullable();
			$table->string('stripeEmail')->nullable();
			$table->string('card_brand', 25)->nullable();
			$table->string('card_last_four', 4)->nullable();
			$table->dateTime('trial_ends_at')->nullable();
			$table->boolean('isOrgUser')->default(0);
			$table->timestamp('subscription_ends_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->boolean('ticketit_admin')->default(0);
			$table->boolean('ticketit_agent')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
