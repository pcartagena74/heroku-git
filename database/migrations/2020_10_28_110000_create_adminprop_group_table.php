<?php
/**
 * Comment: Group Table for Admin Properties
 * Created: 10/28/2020
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPropGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('admin_group', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name', 30);
            $table->integer('order')->default(0);
            $table->string('viewname', 255)->default('viewname');
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_group');
    }
}