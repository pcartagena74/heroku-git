<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgPersonTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('org-person', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('orgID')->index('op-orgID_idx');
			$table->integer('personID')->unsigned()->index('op-personID_idx');
			$table->string('chapterRole', 100)->nullable();
			$table->boolean('isAdmin')->default(0);
			$table->integer('adminLevel')->default(0);
			$table->string('OrgStat1', 45)->nullable();
			$table->string('OrgStat2', 45)->nullable();
			$table->string('OrgStat3', 45)->nullable();
			$table->string('OrgStat4', 45)->nullable();
			$table->string('OrgStat5', 45)->nullable();
			$table->string('OrgStat6', 45)->nullable();
			$table->string('OrgStat7', 45)->nullable();
			$table->string('OrgStat8', 45)->nullable();
			$table->string('OrgStat9', 45)->nullable();
			$table->string('OrgStat10', 45)->nullable();
			$table->dateTime('RelDate1')->nullable();
			$table->dateTime('RelDate2')->nullable();
			$table->dateTime('RelDate3')->nullable();
			$table->dateTime('RelDate4')->nullable();
			$table->dateTime('RelDate5')->nullable();
			$table->dateTime('RelDate6')->nullable();
			$table->dateTime('RelDate7')->nullable();
			$table->dateTime('RelDate8')->nullable();
			$table->dateTime('RelDate9')->nullable();
			$table->dateTime('RelDate10')->nullable();
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
		Schema::drop('org-person');
	}

}
