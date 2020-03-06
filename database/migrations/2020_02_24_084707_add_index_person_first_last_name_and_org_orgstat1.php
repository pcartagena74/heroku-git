<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexPersonFirstLastNameAndOrgOrgstat1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('org-person', function (Blueprint $table) {
            $table->index('OrgStat1','idx_orgstat1');
        });

        Schema::table('person', function (Blueprint $table) {
            $table->index(['firstName', 'lastName']);

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('org-person', function (Blueprint $table) {
            $table->dropIndex('idx_orgstat1');
        });

        Schema::table('person', function (Blueprint $table) {
            $table->dropIndex(['firstName', 'lastName']);

        });
    }
}
