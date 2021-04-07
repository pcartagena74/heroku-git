<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('org-campaign', function (Blueprint $table) {
            $table->integer('campaignID', true);
            $table->integer('orgID')->nullable();
            $table->integer('emailListID')->nullable()->default(0);
            $table->string('title', 55);
            $table->string('fromName')->nullable();
            $table->string('fromEmail')->nullable();
            $table->string('replyEmail')->nullable();
            $table->string('subject')->default('Enter a subject line');
            $table->string('preheader')->nullable();
            $table->text('content')->nullable();
            $table->string('thumbnail')->nullable();
            $table->dateTime('scheduleDate')->nullable();
            $table->dateTime('sendDate')->nullable();
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
        Schema::drop('org-campaign');
    }
}
