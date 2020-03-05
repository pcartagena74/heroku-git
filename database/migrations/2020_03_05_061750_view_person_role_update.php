<?php

use Illuminate\Database\Migrations\Migration;

class ViewPersonRoleUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE OR REPLACE
                    VIEW `person_role` AS
                    select
                        `role_user`.`user_id` AS `user_id`,
                        `role_user`.`role_id` AS `role_id`,
                        `role_user`.`orgID` AS `org_id`
                    from
                        `role_user`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW person_role");
    }
}
