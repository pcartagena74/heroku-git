<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPersonSocialiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //      DB::raw('alter table `person-socialite` add constraint `ps.personID`
        // foreign key (`personID`) references `person` (`personID`)
        // on delete NO ACTION
        // on update NO ACTION');
        Schema::table('person-socialite', function (Blueprint $table) {
            // not working as ps.personID is parsed as table name thou. its not
            $table->foreign('personID', DB::raw('`ps.personID`'))->references('personID')->on('person')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person-socialite', function (Blueprint $table) {
            $table->dropForeign(DB::raw('`ps.personID`'));
        });
    }
}
