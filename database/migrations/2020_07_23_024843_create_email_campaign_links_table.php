<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailCampaignLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_campaign_links', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('campaign_id')->unsigned();
            $table->string('url', 100);
            $table->integer('unique_clicks')->unsigned()->default(0);
            $table->integer('total_clicks')->unsigned()->default(0);
            $table->dateTime('first_click')->nullable();
            $table->dateTime('last_click')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_campaign_links');
    }
}
