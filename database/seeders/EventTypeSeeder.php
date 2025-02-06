<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $sql = "INSERT INTO `org-event_types`
                VALUES 
                    (1,1,'Chapter Meeting',1,1,'2017-01-10 12:05:03',1,'2017-01-10 12:05:03',NULL),
                    (2,1,'Roundtable',1,1,'2017-01-10 12:05:03',1,'2017-01-10 12:05:03',NULL),
                    (3,1,'PD Day',1,1,'2017-01-10 12:07:02',1,'2017-01-10 12:07:02',NULL),
                    (4,1,'Social Gathering',1,1,'2017-01-10 12:07:02',1,'2017-01-10 12:07:02',NULL),
                    (5,1,'Region Event',1,357,'2018-02-24 21:26:24',1,'2018-02-24 21:26:24',NULL),
                    (9,1,'Job Fair',1,1,'2017-01-10 14:25:42',1,'2017-01-10 14:25:42',NULL),
                    (11,1,'Annual Meeting',1,1,'2017-05-22 01:12:39',1,'2017-05-22 01:12:42',NULL)";

        DB::beginTransaction();
        DB::insert($sql);
        DB::commit();
    }
}
