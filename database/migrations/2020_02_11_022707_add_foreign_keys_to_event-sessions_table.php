<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEventSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event-sessions', function (Blueprint $table) {
            $table->foreign('eventID', 'es-eventID')->references('eventID')->on('org-event')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
        // DB::raw('ALTER table `event-sessions` ADD CONSTRAINT `es-eventID` FOREIGN KEY (`eventID`) REFERENCES `org-event` (`eventID`) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event-sessions', function (Blueprint $table) {
            $table->dropForeign('es-eventID');
        });
    }
}
