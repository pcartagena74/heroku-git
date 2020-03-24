<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailListTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email-list', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('orgID');
			$table->string('listName');
			$table->string('listDesc')->nullable();
			$table->string('foundation', 45)->default('none');
			$table->string('included')->nullable();
			$table->string('excluded')->nullable();
			$table->timestamps();
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
		Schema::drop('email-list');
	}

}
