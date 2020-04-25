<?php

use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        for ($i=1; $i++; $i<=10){
            $sql = "INSERT INTO role_user VALUES (1, $i, 1)";
            DB::insert($sql);
        }
        DB::commit();
    }
}
