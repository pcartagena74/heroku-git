<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('person', function(Blueprint $table)
		{
			$table->integer('personID', true);
			$table->string('prefix', 5)->nullable();
			$table->string('firstName', 50)->nullable();
			$table->string('midName', 50)->nullable();
			$table->string('lastName', 50)->nullable();
			$table->string('suffix', 10)->nullable();
			$table->string('prefName', 50)->nullable();
			$table->string('login', 50)->unique('login');
			$table->integer('defaultOrgID')->default(0);
			$table->integer('defaultOrgPersonID')->default(0);
			$table->text('avatarURL', 16777215)->nullable();
			$table->string('title', 100)->nullable();
			$table->string('compName')->nullable();
			$table->string('indName', 100)->nullable();
			$table->string('experience', 10)->nullable();
			$table->string('allergenInfo')->nullable();
			$table->string('allergenNote')->nullable();
			$table->string('specialNeeds')->nullable();
			$table->string('chapterRole', 100)->nullable();
			$table->string('affiliation')->nullable();
			$table->string('twitterHandle', 25)->nullable();
			$table->string('certifications')->nullable();
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->softDeletes();
			$table->dateTime('lastLoginDate')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('person');
	}

}
