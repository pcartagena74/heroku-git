<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOrgPersonTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('org-person', function(Blueprint $table)
		{
			$table->foreign('orgID', 'op-orgID')->references('orgID')->on('organization')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('personID', 'op-personID')->references('personID')->on('person')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('org-person', function(Blueprint $table)
		{
			$table->dropForeign('op-orgID');
			$table->dropForeign('op-personID');
		});
	}

}
