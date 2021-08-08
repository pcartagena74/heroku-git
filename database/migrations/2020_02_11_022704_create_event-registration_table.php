<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventRegistrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event-registration', function (Blueprint $table) {
            $table->integer('regID', true);
            $table->integer('rfID')->nullable();
            $table->integer('eventID')->index('er-eventID_idx');
            $table->integer('ticketID')->index('er-ticketID_idx');
            $table->integer('personID')->unsigned()->index('er-personID_idx');
            $table->string('reportedIndustry', 100)->nullable();
            $table->text('eventTopics', 65535)->nullable();
            $table->boolean('isFirstEvent')->default(0);
            $table->string('cityState', 75)->nullable();
            $table->boolean('isAuthPDU')->default(0);
            $table->text('eventQuestion', 65535)->nullable();
            $table->string('allergenInfo')->nullable();
            $table->boolean('canNetwork')->default(0);
            $table->string('specialNeeds')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('referalText')->nullable();
            $table->text('eventNotes', 65535)->nullable();
            $table->string('regStatus', 45)->default('In progress');
            $table->string('registeredBy', 175)->nullable();
            $table->string('discountCode', 45)->nullable();
            $table->float('origcost', 10)->nullable()->default(0.00);
            $table->float('subtotal', 10)->nullable()->default(0.00);
            $table->float('ccFee', 10)->default(0.00);
            $table->float('mcentricFee', 6)->default(0.00);
            $table->float('refundAmt', 10)->default(0.00);
            $table->string('membership', 20)->nullable();
            $table->string('token')->nullable();
            $table->text('debugNotes', 16777215)->nullable();
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
        Schema::drop('event-registration');
    }
}
