<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event-discounts', function (Blueprint $table) {
            $table->integer('discountID', true);
            $table->integer('orgID')->index('ed-orgID_idx');
            $table->integer('eventID')->index('ed-eventID_idx');
            $table->string('discountCODE', 45)->index('disCode');
            $table->float('percent', 10, 0)->default(0);
            $table->integer('flatAmt')->default(0);
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
        Schema::drop('event-discounts');
    }
}
