<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgEventTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('org-event', function(Blueprint $table)
		{
			$table->integer('eventID', true);
			$table->integer('orgID')->index('e-orgID_idx');
			$table->string('eventName');
			$table->text('eventDescription')->nullable();
			$table->integer('catID')->nullable()->default(50);
			$table->integer('eventTypeID')->default(0);
			$table->text('eventInfo')->nullable();
			$table->dateTime('eventStartDate')->nullable();
			$table->dateTime('eventEndDate')->nullable();
			$table->string('eventTimeZone', 5)->nullable();
			$table->integer('locationID')->nullable();
			$table->string('contactOrg', 100)->nullable();
			$table->string('contactEmail', 100)->nullable();
			$table->text('contactDetails')->nullable();
			$table->text('postRegInfo', 16777215)->nullable();
			$table->boolean('isActive')->nullable()->default(0);
			$table->boolean('isPrivate')->nullable()->default(0);
			$table->boolean('showLogo')->nullable()->default(1);
			$table->string('image1')->nullable();
			$table->string('image2')->nullable();
			$table->string('slug', 100)->unique('vanityURL');
			$table->boolean('recurEvent')->nullable()->default(0);
			$table->integer('recurFrequency')->default(0);
			$table->dateTime('recurEndDate')->nullable();
			$table->boolean('earlyDiscount')->nullable()->default(0);
			$table->dateTime('earlyBirdDate')->nullable();
			$table->boolean('hasBundles')->default(0);
			$table->boolean('hasFood')->default(0);
			$table->boolean('guestsOK')->default(0);
			$table->boolean('hasTracks')->default(0);
			$table->text('refundNote', 16777215)->nullable();
			$table->boolean('isNonRefundable')->default(0);
			$table->integer('confDays')->default(0);
			$table->string('eventTags')->nullable();
			$table->boolean('isSymmetric')->default(1);
			$table->integer('mainSession')->nullable();
			$table->boolean('acceptsCash')->default(0);
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->boolean('isDeleted')->default(0);
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
		Schema::drop('org-event');
	}

}
