<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_history', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('revision');
            $table->string('action', 45);
            $table->dateTime('changeDate');
            $table->integer('id')->default(0);
            $table->string('login');
            $table->string('name')->nullable();
            $table->string('password')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->boolean('ticketit_admin')->nullable()->default(0);
            $table->boolean('ticketit_agent')->nullable()->default(0);
            $table->dateTime('last_login')->nullable();
            $table->string('email')->nullable();
            $table->string('stripe_id')->nullable();
            $table->string('stripeEmail')->nullable();
            $table->string('card_brand', 25)->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->dateTime('trial_ends_at')->nullable();
            $table->boolean('isOrgUser')->default(0);
            $table->timestamp('subscription_ends_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->primary(['revision', 'action', 'changeDate', 'id']);
            $table->increments('revision')->change();
        });
        // DB::statement('CREATE INDEX `rev_act_cd_IDX` USING BTREE ON `users_history` (`revision`,`action`,`changeDate`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_history');
    }
}
