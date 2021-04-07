<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEmailQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_queue', function (Blueprint $table) {
            $table->bigIncrements('id')->increments();
            $table->unsignedInteger('campaign_id');
            $table->unsignedInteger('org_id');
            $table->string('email_id');
            $table->string('message_id')->length(255)->nullable();
            $table->boolean('sent')->default(0);
            $table->boolean('failed')->default(0);
            $table->boolean('click')->default(0);
            $table->boolean('delivered')->default(0);
            $table->boolean('open')->default(0);
            $table->boolean('permanent_fail')->default(0);
            $table->boolean('spam')->default(0);
            $table->boolean('temporary_failure')->default(0);
            $table->boolean('unsubscribe')->default(0);
            $table->datetime('scheduled_datetime')->nullable();
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
        Schema::dropIfExists('email_queue');
    }
}
