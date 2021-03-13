<?php
/**
 * Comment: Admin Properties Table
 * Created: 10/28/2020
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPropTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('admin_prop', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('groupID')->unsigned();
            $table->integer('order');
            $table->string('name', 30);
            $table->string('displayName', 45);
            $table->string('type', 30);
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('groupID')->references('id')->on('admin_group');
            $table->unique(['groupID', 'order']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_prop');
    }
}