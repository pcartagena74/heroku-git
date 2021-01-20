<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganizationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('organization', function(Blueprint $table)
		{
			$table->integer('orgID', true);
			$table->string('orgName', 50);
			$table->string('formalName')->nullable();
			$table->string('orgAddr1')->nullable();
			$table->string('orgAddr2')->nullable();
			$table->string('orgCity', 100)->nullable();
			$table->string('orgState', 2)->nullable();
			$table->string('orgZip', 10)->nullable();
			$table->string('orgPhone', 20)->nullable();
			$table->string('orgFax', 20)->nullable();
			$table->string('orgEmail')->nullable();
			$table->string('orgURL')->nullable();
			$table->string('orgPath');
			$table->string('orgLogo')->nullable();
			$table->string('orgHandle', 50)->nullable();
			$table->string('facebookURL')->nullable();
			$table->string('linkedinURL')->nullable();
			$table->string('googleURL')->nullable();
            $table->unsignedInteger('heatMapZoomLevel')->default(7);
            $table->unsignedInteger('heatMapDensity')->default(5);
			$table->string('defaultTicketLabel', 100)->nullable()->default('Event Ticket');
			$table->string('creditLabel', 25)->default('Credit');
			$table->string('orgZone', 5)->default('-0');
			$table->integer('earlyBirdPercent')->default(0);
			$table->integer('orgCategory')->nullable()->default(50);
			$table->string('anonCats')->nullable();
			$table->string('adminEmail')->nullable();
			$table->string('eventEmail')->nullable();
			$table->text('fullTXT', 16777215)->nullable();
			$table->text('waitTXT', 16777215)->nullable();
			$table->text('noSwitchTEXT', 65535)->nullable();
			$table->string('currency', 5)->default('USD');
			$table->integer('refundDays')->default(14);
			$table->integer('postEventEditDays')->default(30);
			$table->string('nearbyChapters')->nullable();
			$table->text('regionChapters', 65535)->nullable();
			$table->string('discountChapters')->nullable();
			$table->string('adminContactStatement')->nullable();
			$table->text('techContactStatement', 65535)->nullable();
            $table->string('chapPDUReportStatement')->nullable();
			$table->boolean('useExprDates')->default(0);
			$table->string('canSubmitPDU')->nullable()->comment('comma-separated list of eventTypeIDs that the chapter will submit PDUs to PMI if authorized by registrant');
			$table->string('OSN1', 45)->nullable();
			$table->string('OSN2', 45)->nullable();
			$table->string('OSN3', 45)->nullable();
			$table->string('OSN4', 45)->nullable();
			$table->string('OSN5', 45)->nullable();
			$table->string('OSN6', 45)->nullable();
			$table->string('OSN7', 45)->nullable();
			$table->string('OSN8', 45)->nullable();
			$table->string('OSN9', 45)->nullable();
			$table->string('OSN10', 45)->nullable();
			$table->string('ODN1', 45)->nullable();
			$table->string('ODN2', 45)->nullable();
			$table->string('ODN3', 45)->nullable();
			$table->string('ODN4', 45)->nullable();
			$table->string('ODN5', 45)->nullable();
			$table->string('ODN6', 45)->nullable();
			$table->string('ODN7', 45)->nullable();
			$table->string('ODN8', 45)->nullable();
			$table->string('ODN9', 45)->nullable();
			$table->string('ODN10', 45)->nullable();
			$table->integer('creatorID')->default(1);
			$table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('updaterID')->default(1);
			$table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->unique(['orgName','orgHandle'], 'orgName');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('organization');
	}

}
