<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgPersonHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('org-person_history', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('revision');
            $table->string('action', 45)->default('update');
            $table->timestamp('changeDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('id');
            $table->integer('orgID');
            $table->integer('personID');
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
            $table->primary(['revision', 'action', 'changeDate', 'id', 'orgID', 'personID'], 'r_a_c_id_org_pid_key');
            $table->increments('revision')->change(); //make it auto increment
        });
        // DB::statement('CREATE INDEX `rev_act_cd_id_org_per_IDX` USING BTREE ON `org-person_history` (`revision`,`action`,`changeDate`,`id`,`orgId`,`personID`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('org-person_history');
    }
}
