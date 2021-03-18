<?php
/**
 * Comment: Admin Property Settings for Organizations (Pivot table)
 * Created: 10/28/2020
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrgAdminPropTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('org-admin_prop', function (Blueprint $table) {
            $table->integer('orgID');
            $table->integer('propID')->unsigned();
            $table->string('value', 255); //->nullable();
            $table->timestamp('createDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updateDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('orgID')->references('orgID')->on('organization');
            $table->foreign('propID')->references('id')->on('admin_prop');
            $table->unique(['orgID', 'propID']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('org-admin_prop');
    }
}