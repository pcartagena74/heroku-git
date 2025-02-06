<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CertificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $sql = "INSERT INTO `certifications` VALUES
                    (1,'PMP'),(2,'ACP'),(3,'RMP'),(4,'SP'),(5,'PBA'),(6,'PgMP'),(7,'PfMP'),(8,'CAPM'), (10,'N/A');";

        DB::beginTransaction();
        DB::insert($sql);
        DB::commit();

        $sql = 'UPDATE `certifications` SET id=0 where id=10;';
        DB::update($sql);
    }
}
