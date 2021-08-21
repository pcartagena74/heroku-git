<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stmt = "SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'";

        $sql = "INSERT INTO `event-location`
                VALUES 
                    (0,1,'TBD',null,null,null,null,null,228,null,0,1,'2017-01-10 12:05:03',1,'2017-01-10 12:05:03',NULL,0),
                    (1,1,'Webinar / Virtual Event',null,null,null,null,null,228,null,1,1,'2017-01-10 12:05:03',1,'2017-01-10 12:05:03',NULL,0)";

        DB::beginTransaction();
        DB::statement($stmt);
        DB::insert($sql);
        DB::commit();
    }
}
