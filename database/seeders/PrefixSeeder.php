<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrefixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $sql = "INSERT INTO `prefixes` VALUES (1,'Dr'),(2,'Mr'),(3,'Mrs'),(4,'Ms'),(5,'Prof'),(6,'Rev');";

        DB::beginTransaction();
        DB::insert($sql);
        DB::commit();
    }
}
