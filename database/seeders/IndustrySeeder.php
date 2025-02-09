<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sql = "INSERT INTO `industries` VALUES
                    (1,'Aerospace'),(2,'Agriculture'),(3,'Automotive'),(4,'Construction'),(5,'Consulting'),(7,'Defense'),
                    (8,'Education'),(9,'Energy'),(10,'Entertainment'),(11,'Financial Services'),(13,'Government'),
                    (14,'Healthcare'),(15,'High Tech'),(16,'Hospitality'),(18,'Insurance'),(19,'Life Sciences'),
                    (20,'Manufacturing'),(21,'Marketing'),(23,'Other'),(24,'R&D'),(26,'Retail'),(28,'Telecommunications'),
                    (29,'Transportation'),(30,'HR'),(31,'Project Management'),(32,'Non-Profit'),(33,'Legal');";

        DB::beginTransaction();
        DB::insert($sql);
        DB::commit();
    }
}
