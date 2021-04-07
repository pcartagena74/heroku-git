<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRegSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reg-session', function (Blueprint $table) {
            $table->foreign('sessionID', 'rs-sessionID')->references('sessionID')->on('event-sessions')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('eventID', 'rs-eventID')->references('eventID')->on('org-event')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('personID', 'rs-personID')->references('personID')->on('person')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('regID', 'rs-regID')->references('regID')->on('event-registration')->onUpdate('NO ACTION')->onDelete('CASCADE');
            //          DB::raw('alter table `reg-session` add constraint `rs-sessionID`
            // foreign key (`personID`) references `event-sessions` (`sessionID`)
            // on delete CASCADE
            // on update NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reg-session', function (Blueprint $table) {
            $table->dropForeign('rs-sessionID');
            $table->dropForeign('rs-eventID');
            $table->dropForeign('rs-personID');
            $table->dropForeign('rs-regID');
        });
    }
}
